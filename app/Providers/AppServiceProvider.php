<?php

namespace App\Providers;

use App\Repository\CompetitionRepository;
use App\Repository\CompetitionRepositoryInterface;
use App\Repository\FieldRepository;
use App\Repository\FieldRepositoryInterface;
use App\Repository\EventRepository;
use App\Repository\ButtonRepository;
use App\Repository\MemberRepository;
use App\Repository\PromocodeRepository;
use App\Repository\PromocodeRepositoryInterface;
use App\Repository\SpeakerRepository;
use App\Repository\TelegramRepository;
use App\Repository\TelegramRepositoryInterface;
use App\Repository\TemplateRepository;
use Illuminate\Support\ServiceProvider;
use App\Repository\InlineButtonRepository;
use App\Repository\EventRepositoryInterface;
use App\Repository\ButtonRepositoryInterface;
use App\Repository\MemberRepositoryInterface;
use App\Repository\SpeakerRepositoryInterface;
use App\Repository\TemplateRepositoryInterface;
use App\Repository\InlineButtonRepositoryInterface;
use App\Repository\UserRepository;
use App\Repository\UserRepositoryInterface;
use App\Repository\TaskRepository;
use App\Repository\TaskRepositoryInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(EventRepositoryInterface::class, EventRepository::class);
        $this->app->bind(MemberRepositoryInterface::class, MemberRepository::class);
        $this->app->bind(TemplateRepositoryInterface::class, TemplateRepository::class);
        $this->app->bind(InlineButtonRepositoryInterface::class, InlineButtonRepository::class);
        $this->app->bind(ButtonRepositoryInterface::class, ButtonRepository::class);
        $this->app->bind(SpeakerRepositoryInterface::class, SpeakerRepository::class);
        $this->app->bind(FieldRepositoryInterface::class, FieldRepository::class);
        $this->app->bind(TelegramRepositoryInterface::class, TelegramRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(CompetitionRepositoryInterface::class, CompetitionRepository::class);
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
        $this->app->bind(PromocodeRepositoryInterface::class, PromocodeRepository::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
