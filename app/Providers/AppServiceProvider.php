<?php

namespace App\Providers;

use App\Models\Avenant;
use App\Models\Chantier;
use App\Models\Document;
use App\Models\Equipement;
use App\Models\Finance;
use App\Models\Materiau;
use App\Models\Personnel;
use App\Models\Presence;
use App\Observers\AvenantObserver;
use App\Observers\ChantierObserver;
use App\Observers\DocumentObserver;
use App\Observers\FinanceObserver;
use App\Observers\PresenceObserver;
use App\Policies\AvenantPolicy;
use App\Policies\ChantierPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\EquipementPolicy;
use App\Policies\FinancePolicy;
use App\Policies\MateriauPolicy;
use App\Policies\PersonnelPolicy;
use App\Policies\PresencePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        // --- Observers (declencheurs metier automatiques) ---
        Chantier::observe(ChantierObserver::class);
        Presence::observe(PresenceObserver::class);
        Avenant::observe(AvenantObserver::class);
        Finance::observe(FinanceObserver::class);
        Document::observe(DocumentObserver::class);

        // --- Policies ---
        Gate::policy(Chantier::class, ChantierPolicy::class);
        Gate::policy(Personnel::class, PersonnelPolicy::class);
        Gate::policy(Presence::class, PresencePolicy::class);
        Gate::policy(Materiau::class, MateriauPolicy::class);
        Gate::policy(Equipement::class, EquipementPolicy::class);
        Gate::policy(Finance::class, FinancePolicy::class);
        Gate::policy(Avenant::class, AvenantPolicy::class);
        Gate::policy(Document::class, DocumentPolicy::class);
    }
}
