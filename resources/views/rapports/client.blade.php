@php
    $fcfa = fn ($montant) => number_format((float) $montant, 0, ',', ' ') . ' FCFA';
    $dateFr = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') : '-';
    $libelleStatut = [
        'en_attente' => 'En attente', 'en_cours' => 'En cours', 'en_pause' => 'En pause',
        'termine' => 'Termine', 'archive' => 'Archive',
    ];
    $libelleContrat = [
        'cdi' => 'CDI', 'cdd' => 'CDD', 'journalier' => 'Journalier', 'prestataire' => 'Prestataire',
    ];
    $budget = (float) ($chantier->budget_consolide ?? $chantier->budget_initial);
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: 'DejaVu Sans', sans-serif; }
        body { font-size: 11px; color: #1f2937; margin: 0; }
        .header { border-bottom: 3px solid #c2410c; padding-bottom: 12px; margin-bottom: 18px; }
        .header table { width: 100%; }
        .logo { font-size: 22px; font-weight: bold; color: #c2410c; }
        .logo span { color: #1f2937; }
        .societe { font-size: 10px; color: #6b7280; }
        .doc-titre { text-align: right; font-size: 16px; font-weight: bold; color: #1f2937; }
        .doc-meta { text-align: right; font-size: 9px; color: #6b7280; }
        h2 {
            font-size: 13px; color: #c2410c; border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px; margin: 18px 0 8px;
        }
        table.data { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        table.data th {
            background: #c2410c; color: #fff; text-align: left;
            padding: 5px 7px; font-size: 10px;
        }
        table.data td { padding: 4px 7px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
        table.data tr:nth-child(even) td { background: #fef3ec; }
        .infos td { padding: 4px 6px; font-size: 11px; }
        .infos .label { color: #6b7280; width: 28%; }
        .infos .val { font-weight: bold; }
        .kpis { width: 100%; margin: 6px 0 4px; }
        .kpis td {
            width: 25%; text-align: center; padding: 10px 6px;
            border: 1px solid #e5e7eb; background: #f9fafb;
        }
        .kpis .montant { font-size: 14px; font-weight: bold; color: #1f2937; }
        .kpis .libelle { font-size: 9px; color: #6b7280; text-transform: uppercase; }
        .bar-wrap { background: #e5e7eb; height: 14px; border-radius: 3px; width: 100%; }
        .bar { height: 14px; border-radius: 3px; background: #16a34a; }
        .bar.alerte { background: #dc2626; }
        .badge {
            display: inline-block; padding: 2px 8px; border-radius: 10px;
            font-size: 9px; font-weight: bold; background: #fef3ec; color: #c2410c;
        }
        .text-right { text-align: right; }
        .vide { color: #9ca3af; font-style: italic; padding: 6px 0; }
        .footer {
            position: fixed; bottom: -10px; left: 0; right: 0;
            border-top: 1px solid #e5e7eb; padding-top: 6px;
            font-size: 8px; color: #9ca3af; text-align: center;
        }
        .signature { margin-top: 30px; width: 100%; }
        .signature td { width: 50%; padding-top: 30px; font-size: 10px; }
        .signature .ligne { border-top: 1px solid #6b7280; width: 60%; padding-top: 4px; }
    </style>
</head>
<body>

    <div class="header">
        <table>
            <tr>
                <td>
                    <div class="logo">BTP <span>MASTER PRO</span></div>
                    <div class="societe">MS-GROUP AFRIQUE &middot; Gestion de chantiers</div>
                </td>
                <td>
                    <div class="doc-titre">RAPPORT CLIENT</div>
                    <div class="doc-meta">
                        Genere le {{ $genere_le->format('d/m/Y a H:i') }}<br>
                        Par {{ $genere_par->nom_complet ?? 'Administrateur' }}
                        @if($periode['debut'] || $periode['fin'])
                            <br>Periode : {{ $dateFr($periode['debut']) }} au {{ $dateFr($periode['fin']) }}
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    @if(in_array('infos', $sections))
        <h2>Informations generales</h2>
        <table class="infos">
            <tr>
                <td class="label">Chantier</td>
                <td class="val">{{ $chantier->nom }}</td>
                <td class="label">Reference</td>
                <td class="val">{{ $chantier->reference ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Maitre d'ouvrage</td>
                <td class="val">{{ $chantier->maitre_ouvrage ?? '-' }}</td>
                <td class="label">Statut</td>
                <td class="val"><span class="badge">{{ $libelleStatut[$chantier->statut] ?? $chantier->statut }}</span></td>
            </tr>
            <tr>
                <td class="label">Adresse</td>
                <td class="val">{{ $chantier->adresse ?? '-' }}</td>
                <td class="label">Chef de chantier</td>
                <td class="val">{{ $chantier->chef?->nom_complet ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Debut prevu</td>
                <td class="val">{{ $dateFr($chantier->date_debut_prevue) }}</td>
                <td class="label">Fin prevue</td>
                <td class="val">{{ $dateFr($chantier->date_fin_prevue) }}</td>
            </tr>
        </table>
    @endif

    @if(in_array('avancement', $sections))
        <h2>Avancement &amp; synthese financiere</h2>
        <table class="kpis">
            <tr>
                <td>
                    <div class="montant">{{ $fcfa($budget) }}</div>
                    <div class="libelle">Budget consolide</div>
                </td>
                <td>
                    <div class="montant">{{ $fcfa($chantier->depenses_engagees) }}</div>
                    <div class="libelle">Depenses engagees</div>
                </td>
                <td>
                    <div class="montant">{{ $fcfa($chantier->solde) }}</div>
                    <div class="libelle">Solde disponible</div>
                </td>
                <td>
                    <div class="montant">{{ $chantier->pourcentage_consomme }} %</div>
                    <div class="libelle">Budget consomme</div>
                </td>
            </tr>
        </table>
        @php $pct = min(100, (float) $chantier->pourcentage_consomme); @endphp
        <p style="margin:4px 0 2px; font-size:10px; color:#6b7280;">Consommation budgetaire</p>
        <div class="bar-wrap">
            <div class="bar {{ $pct >= 80 ? 'alerte' : '' }}" style="width: {{ $pct }}%;"></div>
        </div>
        @if($pct >= 80)
            <p style="color:#dc2626; font-size:10px; margin-top:4px;">
                &#9888; Seuil d'alerte atteint : {{ $chantier->pourcentage_consomme }} % du budget consomme.
            </p>
        @endif
        <p style="margin-top:8px; font-size:10px;">
            Taux d'avancement estime : <strong>{{ $chantier->taux_avancement }} %</strong>
        </p>
    @endif

    @if(in_array('finances', $sections))
        <h2>Detail des operations financieres validees</h2>
        @if($donnees['finances']->isEmpty())
            <p class="vide">Aucune operation financiere validee sur la periode.</p>
        @else
            <table class="data">
                <thead>
                    <tr>
                        <th>Date</th><th>Libelle</th><th>Type</th><th>Categorie</th><th class="text-right">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($donnees['finances'] as $f)
                        <tr>
                            <td>{{ $dateFr($f->date_operation) }}</td>
                            <td>{{ $f->libelle }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $f->type_operation)) }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $f->categorie)) }}</td>
                            <td class="text-right">{{ $fcfa($f->montant) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="4" class="text-right"><strong>Total depenses</strong></td>
                        <td class="text-right"><strong>{{ $fcfa($donnees['total_depenses']) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        @endif
    @endif

    @if(in_array('personnel', $sections))
        <h2>Personnel affecte</h2>
        @if($donnees['personnel']->isEmpty())
            <p class="vide">Aucun personnel enregistre.</p>
        @else
            <table class="data">
                <thead>
                    <tr><th>Nom complet</th><th>Poste</th><th>Contrat</th><th class="text-right">Taux/jour</th><th>Entree</th></tr>
                </thead>
                <tbody>
                    @foreach($donnees['personnel'] as $p)
                        <tr>
                            <td>{{ $p->prenom }} {{ $p->nom }}</td>
                            <td>{{ $p->poste }}</td>
                            <td>{{ $libelleContrat[$p->type_contrat] ?? $p->type_contrat }}</td>
                            <td class="text-right">{{ $p->taux_journalier ? $fcfa($p->taux_journalier) : '-' }}</td>
                            <td>{{ $dateFr($p->date_entree) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endif

    @if(in_array('materiaux', $sections))
        <h2>Materiaux</h2>
        @if($donnees['materiaux']->isEmpty())
            <p class="vide">Aucun materiau enregistre.</p>
        @else
            <table class="data">
                <thead>
                    <tr>
                        <th>Designation</th><th>Fournisseur</th><th class="text-right">Cmd.</th>
                        <th class="text-right">Recu</th><th class="text-right">Stock</th><th class="text-right">Cout total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($donnees['materiaux'] as $m)
                        <tr>
                            <td>{{ $m->designation }}</td>
                            <td>{{ $m->fournisseur ?? '-' }}</td>
                            <td class="text-right">{{ rtrim(rtrim(number_format($m->quantite_commandee, 2, ',', ' '), '0'), ',') }} {{ $m->unite }}</td>
                            <td class="text-right">{{ rtrim(rtrim(number_format($m->quantite_recue, 2, ',', ' '), '0'), ',') }}</td>
                            <td class="text-right">{{ rtrim(rtrim(number_format($m->stock_restant, 2, ',', ' '), '0'), ',') }}</td>
                            <td class="text-right">{{ $fcfa($m->cout_total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endif

    @if(in_array('equipements', $sections))
        <h2>Equipements</h2>
        @if($donnees['equipements']->isEmpty())
            <p class="vide">Aucun equipement enregistre.</p>
        @else
            <table class="data">
                <thead>
                    <tr>
                        <th>Nom</th><th>Mise a dispo.</th><th>Etat</th>
                        <th class="text-right">Cout/jour</th><th class="text-right">Cout location</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($donnees['equipements'] as $e)
                        <tr>
                            <td>{{ $e->nom }}{{ $e->reference ? ' ('.$e->reference.')' : '' }}</td>
                            <td>{{ ucfirst($e->type_mise_dispo) }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $e->etat)) }}</td>
                            <td class="text-right">{{ $e->cout_journalier ? $fcfa($e->cout_journalier) : '-' }}</td>
                            <td class="text-right">{{ $e->type_mise_dispo === 'location' ? $fcfa($e->cout_total_location) : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endif

    @if(in_array('documents', $sections))
        <h2>Documents cles</h2>
        @if($donnees['documents']->isEmpty())
            <p class="vide">Aucun document valide.</p>
        @else
            <table class="data">
                <thead>
                    <tr><th>Titre</th><th>Type</th><th>Date d'ajout</th></tr>
                </thead>
                <tbody>
                    @foreach($donnees['documents'] as $d)
                        <tr>
                            <td>{{ $d->titre }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $d->type_document)) }}</td>
                            <td>{{ $dateFr($d->date_upload) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endif

    @if(in_array('observations', $sections) && !empty($observations))
        <h2>Observations</h2>
        <p style="font-size:11px; line-height:1.5;">{!! nl2br(e($observations)) !!}</p>
    @endif

    <table class="signature">
        <tr>
            <td><div class="ligne">Le chef de chantier</div></td>
            <td><div class="ligne">La direction MS-GROUP AFRIQUE</div></td>
        </tr>
    </table>

    <div class="footer">
        BTP MASTER PRO &middot; MS-GROUP AFRIQUE &middot; Document genere automatiquement &middot;
        {{ $chantier->nom }} ({{ $chantier->reference ?? 'N/A' }})
    </div>

</body>
</html>
