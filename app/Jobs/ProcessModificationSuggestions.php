<?php

namespace App\Jobs;

use App\Models\ModificationSuggestion;
use App\Models\Work;
use App\Models\Edition;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcessModificationSuggestions implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $suggestions = ModificationSuggestion::where('status', 'pending')->get();

        foreach ($suggestions as $suggestion) {
            DB::transaction(function () use ($suggestion) {
                $this->processSuggestion($suggestion);
            });
        }
    }

    private function processSuggestion(ModificationSuggestion $suggestion): void
    {
        $target = $suggestion->target;
        if (!$target) {
            $suggestion->update(['status' => 'rejected']);
            return;
        }

        // 1. Calculer le nombre d'utilisateurs ayant l'oeuvre dans leur bibliothèque
        $workId = $target instanceof Work ? $target->id : ($target instanceof Edition ? $target->work_id : null);
        if (!$workId) return;

        // On compte les users qui ont une lecture pour cette oeuvre (ou une édition de cette oeuvre)
        $totalUsersWithWork = DB::table('readings')
            ->where(function($query) use ($workId) {
                $query->where('work_id', $workId)
                      ->orWhereIn('edition_id', function($q) use ($workId) {
                          $q->select('id')->from('editions')->where('work_id', $workId);
                      });
            })
            ->distinct('user_id')
            ->count('user_id');

        $totalUsersWithWork = max($totalUsersWithWork, 1); // Au moins 1 (le créateur)

        $upvotes = $suggestion->upvotes_count;
        $downvotes = $suggestion->downvotes_count;
        $totalVotes = $upvotes + $downvotes;

        if ($totalVotes === 0) {
            // Rejet automatique après 1 mois s'il n'y a aucun vote
            if (Carbon::parse($suggestion->created_at)->diffInMonths(now()) >= 1) {
                $suggestion->update(['status' => 'rejected']);
            }
            return;
        }

        // 2. Cas Litigieux (Egalité ou quasi-égalité)
        // Différence <= 10% du total des votes
        $difference = abs($upvotes - $downvotes);
        if (($difference / $totalVotes) <= 0.10) {
            // Passe en arbitrage admin
            $suggestion->update(['status' => 'manual_review']);
            return; 
        }

        // 3. Seuil de participation demandé : entre 40% et 50%
        // On fixe la barre d'approbation automatique à 40% des lecteurs
        $approvalThreshold = $totalUsersWithWork * 0.40;
        
        $hasReachedThreshold = $upvotes >= $approvalThreshold;
        $isOlderThanOneMonth = Carbon::parse($suggestion->created_at)->diffInMonths(now()) >= 1;

        // 4. Validation
        if ($hasReachedThreshold && $upvotes > $downvotes) {
            $this->approveSuggestion($suggestion, $target);
        } elseif ($isOlderThanOneMonth && $upvotes > $downvotes) {
            // Validation par défaut si plus d'un mois et majorité pour (même sans le quorum)
            $this->approveSuggestion($suggestion, $target);
        } elseif ($isOlderThanOneMonth && $downvotes >= $upvotes) {
            // Rejet si plus d'un mois et majorité contre
            $suggestion->update(['status' => 'rejected']);
        }
    }

    private function approveSuggestion(ModificationSuggestion $suggestion, $target): void
    {
        $field = $suggestion->field;
        $newValue = $suggestion->suggested_value;
        $oldValue = $target->{$field};

        // Sauvegarder l'historique (système)
        $target->revisionHistories()->create([
            'user_id' => null, 
            'old_values' => [$field => $oldValue],
            'new_values' => [$field => $newValue],
        ]);

        // Mettre à jour la cible
        $target->{$field} = $newValue;
        $target->save();

        $suggestion->update(['status' => 'approved']);
    }
}
