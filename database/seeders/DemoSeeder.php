<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\ChecklistItem;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $ali = User::updateOrCreate(
            ['email' => 'ali@taskly.test'],
            ['name' => 'Ali Demo', 'password' => Hash::make('Password123')]
        );
        $sara = User::updateOrCreate(
            ['email' => 'sara@taskly.test'],
            ['name' => 'Sara Demo', 'password' => Hash::make('Password123')]
        );

        $site = Board::create([
            'name' => 'Site web',
            'description' => 'Refonte du site vitrine',
            'owner_id' => $ali->id,
        ]);
        $mobile = Board::create([
            'name' => 'Application mobile',
            'description' => "Première version de l'application",
            'owner_id' => $sara->id,
        ]);
        $site->members()->attach([$ali->id, $sara->id]);
        $mobile->members()->attach([$sara->id, $ali->id]);

        // [board, titre, statut, priorité, échéance (jours à partir d'aujourd'hui), assignée à]
        $rows = [
            [$site, "Maquettes de la page d'accueil", 'done', 'high', -6, $ali],
            [$site, 'Choisir la palette de couleurs', 'done', 'low', -4, $sara],
            [$site, 'Intégrer le menu de navigation', 'done', 'medium', -2, $ali],
            [$site, 'Rédiger les textes de la page À propos', 'in_progress', 'medium', 2, $sara],
            [$site, 'Formulaire de contact', 'in_progress', 'high', 1, $ali],
            [$site, 'Optimiser les images', 'todo', 'low', 7, $sara],
            [$site, 'Mettre en place le suivi des visites', 'todo', 'medium', 10, $ali],
            [$site, 'Corriger les liens cassés', 'todo', 'high', -1, $ali],
            [$mobile, 'Écran de connexion', 'done', 'high', -8, $sara],
            [$mobile, 'Liste des tâches', 'in_progress', 'high', 3, $sara],
            [$mobile, 'Notifications push', 'todo', 'medium', 14, $ali],
            [$mobile, "Icône de l'application", 'done', 'low', -3, $ali],
            [$mobile, 'Mode sombre', 'todo', 'low', 21, $sara],
            [$mobile, 'Tests sur Android', 'in_progress', 'medium', -2, $ali],
            [$mobile, 'Publier sur le store', 'todo', 'high', 30, $sara],
        ];

        foreach ($rows as [$board, $title, $status, $priority, $days, $assignee]) {
            Task::create([
                'board_id' => $board->id,
                'created_by' => $board->owner_id,
                'assigned_to' => $assignee->id,
                'title' => $title,
                'description' => 'Tâche de démonstration.',
                'status' => $status,
                'priority' => $priority,
                'due_date' => now()->addDays($days)->toDateString(),
            ]);
        }

        // Une checklist sur une tâche en cours, pour la démo.
        $task = Task::where('title', 'Formulaire de contact')->first();
        foreach (['Dessiner le formulaire', 'Valider les champs', 'Tester sur mobile'] as $i => $label) {
            ChecklistItem::create(['task_id' => $task->id, 'label' => $label, 'done' => $i === 0]);
        }
    }
}
