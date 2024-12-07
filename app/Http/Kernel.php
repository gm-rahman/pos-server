<?php 

protected $middlewareAliases = [
   
    'ability' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
    'ability.all' => \Laravel\Sanctum\Http\Middleware\CheckAbilitiesForAll::class,
];  