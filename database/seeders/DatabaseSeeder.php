<?php

namespace Database\Seeders;

use App\Models\Avenant;
use App\Models\Chantier;
use App\Models\Document;
use App\Models\Equipement;
use App\Models\Finance;
use App\Models\Materiau;
use App\Models\Personnel;
use App\Models\Presence;
use App\Models\Utilisateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Comptes de demonstration ---
        $admin = Utilisateur::firstOrCreate(
            ['email' => 'admin@btp.com'],
            [
                'nom'          => 'Mensah',
                'prenom'       => 'Kofi',
                'telephone'    => '+225 07 00 00 00 01',
                'mot_de_passe' => Hash::make('password'),
                'role'         => 'admin',
                'actif'        => true,
            ]
        );

        $chef = Utilisateur::firstOrCreate(
            ['email' => 'chef@btp.com'],
            [
                'nom'          => 'Diallo',
                'prenom'       => 'Amadou',
                'telephone'    => '+225 07 00 00 00 02',
                'mot_de_passe' => Hash::make('password'),
                'role'         => 'chef_chantier',
                'actif'        => true,
            ]
        );

        // --- Chantier 1 : Villa duplex a Cocody (Abidjan) ---
        $villa = Chantier::firstOrCreate(
            ['nom' => 'Construction Villa Duplex - Cocody'],
            [
                'adresse'           => 'Riviera Palmeraie, Cocody, Abidjan',
                'latitude'          => 5.36110000,
                'longitude'         => -3.96810000,
                'date_debut_prevue' => '2026-01-15',
                'date_fin_prevue'   => '2026-09-30',
                'budget_initial'    => 85000000,
                'maitre_ouvrage'    => 'Famille Kouassi',
                'statut'            => 'en_cours',
                'description'       => "Construction d'une villa duplex 5 pieces avec piscine et cloture.",
                'id_chef_chantier'  => $chef->id,
            ]
        );

        // --- Chantier 2 : Voirie a Yopougon (Abidjan) ---
        $voirie = Chantier::firstOrCreate(
            ['nom' => 'Refection Voirie - Yopougon'],
            [
                'adresse'           => 'Quartier Selmer, Yopougon, Abidjan',
                'latitude'          => 5.34530000,
                'longitude'         => -4.07230000,
                'date_debut_prevue' => '2026-03-01',
                'date_fin_prevue'   => '2026-12-15',
                'budget_initial'    => 120000000,
                'maitre_ouvrage'    => 'Mairie de Yopougon',
                'statut'            => 'en_cours',
                'description'       => 'Refection de 3,5 km de voirie urbaine avec assainissement.',
                'id_chef_chantier'  => $chef->id,
            ]
        );

        $this->seedDetailsVilla($villa, $chef);
        $this->seedDetailsVoirie($voirie, $chef);
    }

    private function seedDetailsVilla(Chantier $chantier, Utilisateur $chef): void
    {
        $equipeData = [
            ['nom' => 'Traore',   'prenom' => 'Seydou', 'poste' => 'Chef macon',    'type_contrat' => 'cdi',       'taux_journalier' => 12000],
            ['nom' => 'Kone',     'prenom' => 'Ibrahim','poste' => 'Macon',          'type_contrat' => 'journalier','taux_journalier' => 8000],
            ['nom' => 'Bamba',    'prenom' => 'Moussa', 'poste' => 'Ferrailleur',    'type_contrat' => 'journalier','taux_journalier' => 8500],
            ['nom' => 'Yao',      'prenom' => 'Konan',  'poste' => 'Manoeuvre',      'type_contrat' => 'journalier','taux_journalier' => 5000],
            ['nom' => 'Ouattara', 'prenom' => 'Fatou',  'poste' => 'Manoeuvre',      'type_contrat' => 'journalier','taux_journalier' => 5000],
        ];

        $equipe = collect($equipeData)->map(fn ($p) => Personnel::firstOrCreate(
            ['nom' => $p['nom'], 'prenom' => $p['prenom'], 'id_chantier' => $chantier->id],
            [
                'poste'           => $p['poste'],
                'type_contrat'    => $p['type_contrat'],
                'taux_journalier' => $p['taux_journalier'],
                'date_entree'     => '2026-01-15',
            ]
        ));

        // Feuilles de presence (idempotentes via la cle date+personnel+chantier)
        $jours   = ['2026-05-04', '2026-05-05', '2026-05-06'];
        $statuts = ['present', 'present', 'demi_journee', 'present', 'absent_non_justifie'];

        foreach ($jours as $jour) {
            foreach ($equipe as $i => $personnel) {
                Presence::firstOrCreate(
                    [
                        'id_personnel' => $personnel->id,
                        'id_chantier'  => $chantier->id,
                        'date_presence'=> $jour,
                    ],
                    ['statut' => $statuts[$i % count($statuts)]]
                );
            }
        }

        // Materiaux
        $materiaux = [
            ['designation' => 'Ciment CPA 42.5 (sac 50kg)',      'quantite_commandee' => 400, 'unite' => 'sac',    'quantite_recue' => 400, 'quantite_utilisee' => 280, 'prix_unitaire' => 5500,   'fournisseur' => 'LafargeHolcim CI'],
            ['designation' => 'Fer a beton 12mm (barre 12m)',     'quantite_commandee' => 250, 'unite' => 'barre',  'quantite_recue' => 250, 'quantite_utilisee' => 180, 'prix_unitaire' => 7200,   'fournisseur' => 'SOTACI'],
            ['designation' => 'Sable lave (camion 10m3)',         'quantite_commandee' => 12,  'unite' => 'camion', 'quantite_recue' => 10,  'quantite_utilisee' => 8,   'prix_unitaire' => 95000,  'fournisseur' => 'Carriere Anyama'],
            ['designation' => 'Gravier 5/15 (camion 10m3)',       'quantite_commandee' => 8,   'unite' => 'camion', 'quantite_recue' => 8,   'quantite_utilisee' => 6,   'prix_unitaire' => 110000, 'fournisseur' => 'Carriere Anyama'],
        ];

        foreach ($materiaux as $m) {
            Materiau::firstOrCreate(
                ['designation' => $m['designation'], 'id_chantier' => $chantier->id],
                [
                    'quantite_commandee' => $m['quantite_commandee'],
                    'unite'              => $m['unite'],
                    'quantite_recue'     => $m['quantite_recue'],
                    'quantite_utilisee'  => $m['quantite_utilisee'],
                    'prix_unitaire'      => $m['prix_unitaire'],
                    'fournisseur'        => $m['fournisseur'],
                    'date_livraison'     => '2026-02-10',
                ]
            );
        }

        // Equipements
        Equipement::firstOrCreate(
            ['reference' => 'BET-350-01', 'id_chantier' => $chantier->id],
            [
                'nom'               => 'Betonniere 350L',
                'type_mise_dispo'   => 'location',
                'fournisseur'       => 'Loca-BTP Abidjan',
                'cout_journalier'   => 15000,
                'date_affectation'  => '2026-02-01',
                'date_retour_prevue'=> '2026-06-30',
                'etat'              => 'bon_etat',
            ]
        );
        Equipement::firstOrCreate(
            ['reference' => 'VIB-02', 'id_chantier' => $chantier->id],
            [
                'nom'              => 'Vibreur a beguille',
                'type_mise_dispo'  => 'propriete',
                'cout_journalier'  => 0,
                'date_affectation' => '2026-02-01',
                'etat'             => 'bon_etat',
            ]
        );

        // Operations financieres
        $this->finance($chantier, $chef, 'Achat ciment - 1ere livraison',       'facture',        'materiaux',   2200000, '2026-02-10', 'valide');
        $this->finance($chantier, $chef, 'Achat fer a beton',                   'facture',        'materiaux',   1800000, '2026-02-12', 'valide');
        $this->finance($chantier, $chef, 'Location betonniere - fevrier',        'depense',        'equipements',  450000, '2026-02-28', 'valide');
        $this->finance($chantier, $chef, 'Avance carburant groupe electrogene',  'avance_acompte', 'divers',       150000, '2026-05-02', 'en_attente');

        // Documents
        $this->document($chantier, $chef, 'Plan architectural - Villa duplex', 'plan',     'valide');
        $this->document($chantier, $chef, 'Contrat de construction signe',     'contrat',  'valide');
        $this->document($chantier, $chef, 'PV reception fondations',           'pv',       'en_attente');

        // Avenant
        $avenant = Avenant::firstOrCreate(
            ['motif' => 'Extension du sous-sol non prevue initialement (demande client).', 'id_chantier' => $chantier->id],
            [
                'montant_demande' => 6500000,
                'statut'          => 'en_attente',
                'id_demandeur'    => $chef->id,
                'date_demande'    => Carbon::parse('2026-04-15'),
            ]
        );

        if ($avenant->statut !== 'approuve') {
            $avenant->update([
                'statut'             => 'approuve',
                'commentaire_admin'  => "Validee apres accord ecrit du maitre d'ouvrage.",
                'date_traitement'    => Carbon::parse('2026-04-18'),
            ]);
        }
    }

    private function seedDetailsVoirie(Chantier $chantier, Utilisateur $chef): void
    {
        $equipeData = [
            ['nom' => 'Cisse',     'prenom' => 'Abou',    'poste' => 'Conducteur engin', 'type_contrat' => 'cdd',       'taux_journalier' => 18000],
            ['nom' => 'Sangare',   'prenom' => 'Drissa',  'poste' => 'Macon VRD',        'type_contrat' => 'journalier','taux_journalier' => 9000],
            ['nom' => 'Toure',     'prenom' => 'Mariam',  'poste' => 'Manoeuvre',        'type_contrat' => 'journalier','taux_journalier' => 5500],
            ['nom' => 'Coulibaly', 'prenom' => 'Lassina', 'poste' => 'Manoeuvre',        'type_contrat' => 'journalier','taux_journalier' => 5500],
        ];

        collect($equipeData)->each(fn ($p) => Personnel::firstOrCreate(
            ['nom' => $p['nom'], 'prenom' => $p['prenom'], 'id_chantier' => $chantier->id],
            [
                'poste'           => $p['poste'],
                'type_contrat'    => $p['type_contrat'],
                'taux_journalier' => $p['taux_journalier'],
                'date_entree'     => '2026-03-01',
            ]
        ));

        $materiaux = [
            ['designation' => "Bitume (futs 200L)",          'quantite_commandee' => 60, 'unite' => 'fut',   'quantite_recue' => 60, 'quantite_utilisee' => 35, 'prix_unitaire' => 145000, 'fournisseur' => "SIR Cote d'Ivoire"],
            ['designation' => 'Tout-venant 0/31.5 (camion)', 'quantite_commandee' => 40, 'unite' => 'camion','quantite_recue' => 30, 'quantite_utilisee' => 22, 'prix_unitaire' => 85000,  'fournisseur' => 'Carriere Songon'],
            ['designation' => 'Buses beton 600mm',           'quantite_commandee' => 80, 'unite' => 'unite', 'quantite_recue' => 80, 'quantite_utilisee' => 50, 'prix_unitaire' => 32000,  'fournisseur' => 'Prefabeton CI'],
        ];

        foreach ($materiaux as $m) {
            Materiau::firstOrCreate(
                ['designation' => $m['designation'], 'id_chantier' => $chantier->id],
                [
                    'quantite_commandee' => $m['quantite_commandee'],
                    'unite'              => $m['unite'],
                    'quantite_recue'     => $m['quantite_recue'],
                    'quantite_utilisee'  => $m['quantite_utilisee'],
                    'prix_unitaire'      => $m['prix_unitaire'],
                    'fournisseur'        => $m['fournisseur'],
                    'date_livraison'     => '2026-03-20',
                ]
            );
        }

        Equipement::firstOrCreate(
            ['reference' => 'COMP-10T', 'id_chantier' => $chantier->id],
            [
                'nom'               => 'Compacteur cylindre 10T',
                'type_mise_dispo'   => 'location',
                'fournisseur'       => 'Loca-BTP Abidjan',
                'cout_journalier'   => 85000,
                'date_affectation'  => '2026-03-05',
                'date_retour_prevue'=> '2026-08-30',
                'etat'              => 'bon_etat',
            ]
        );

        $this->finance($chantier, $chef, 'Achat bitume - phase 1',          'facture', 'materiaux',   5075000, '2026-03-22', 'valide');
        $this->finance($chantier, $chef, 'Location compacteur - mars/avril', 'depense', 'equipements', 2550000, '2026-04-30', 'valide');
        $this->finance($chantier, $chef, 'Achat buses assainissement',       'facture', 'materiaux',   1600000, '2026-04-05', 'en_attente');

        $this->document($chantier, $chef, "Plan d'execution voirie",    'plan',           'valide');
        $this->document($chantier, $chef, 'Fiche securite chantier',    'fiche_securite', 'valide');
    }

    private function finance(Chantier $chantier, Utilisateur $auteur, string $libelle, string $type, string $categorie, float $montant, string $date, string $statut): void
    {
        $finance = Finance::firstOrCreate(
            ['libelle' => $libelle, 'id_chantier' => $chantier->id],
            [
                'type_operation' => $type,
                'montant'        => $montant,
                'date_operation' => $date,
                'categorie'      => $categorie,
                'statut'         => 'en_attente',
                'id_utilisateur' => $auteur->id,
            ]
        );

        if ($statut !== 'en_attente' && $finance->statut === 'en_attente') {
            $finance->update([
                'statut'             => $statut,
                'commentaire_admin'  => $statut === 'valide' ? 'Piece justificative conforme.' : null,
            ]);
        }
    }

    private function document(Chantier $chantier, Utilisateur $auteur, string $titre, string $type, string $statut): void
    {
        $document = Document::firstOrCreate(
            ['titre' => $titre, 'id_chantier' => $chantier->id],
            [
                'type_document'  => $type,
                'fichier'        => 'documents/seed-' . \Illuminate\Support\Str::slug($titre) . '.pdf',
                'statut'         => 'en_attente',
                'id_utilisateur' => $auteur->id,
            ]
        );

        if ($statut !== 'en_attente' && $document->statut === 'en_attente') {
            $document->update(['statut' => $statut]);
        }
    }
}