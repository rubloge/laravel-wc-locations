<?php

namespace Rubloge\LaravelWcLocations;

use Illuminate\Support\Collection;

class LaravelWcLocations
{
    private $continents = [];

    private $countries = [];

    private $states = [];

    private $locale = null;

    private $locales = [
        'en',
        'es',
    ];

    public function __construct($locale = null)
    {
        if (is_null($locale)) {
            $locale = app()->getLocale();
        }
        $this->setLocale($locale);
    }

    public function setLocale(?string $locale = null): void
    {
        if ($locale && in_array($locale, $this->locales) && $locale != 'en') {
            $this->locale = $locale;

            return;
        }
        $this->locale = null;
    }

    public function getContinents(
        bool $with_countries = false,
        bool $with_states = false,
        bool $translate = true
    ): Collection {
        if (empty($this->continents)) {
            $this->continents = require __DIR__.'/../resources/data/continents.php';
        }

        $continents = $this->continents;
        if (($translate && $this->locale) || $with_countries) {
            $copy = new \ArrayObject($continents);
            $continents = $copy->getArrayCopy();
            unset($copy);
        }
        if ($translate && $this->locale) {
            foreach ($continents as $key => $continent) {
                $continents[$key]['name'] = $this->getTranslation($continent['name'], 'continent');
            }
        }
        if ($with_countries) {
            foreach ($continents as $key => $continent) {
                $continents[$key]['countries'] = $this->getCountries(
                    code: $continents[$key]['countries'],
                    with_states: $with_states,
                    translate: $translate
                );
            }
        }

        return collect($continents);
    }

    public function getContinent(
        ?string $code = null,
        bool $with_countries = false,
        bool $with_states = false,
        bool $translate = true
    ): ?object {
        if (! $code) {
            return null;
        }
        $continents = $this->getContinents(translate: false);

        if ($continents->has($code)) {
            $continent = clone $continents->get($code);
            if (! $translate && $this->locale) {
                $continent->name = $this->getTranslation($continent->name, 'continent');
            }
            if ($with_countries) {
                $continent->countries = $this->getCountries(
                    code: $continent->countries,
                    with_states: $with_states,
                    translate: $translate
                );
            }

            return (object) $continent;
        }

        return null;
    }

    public function getCountries(
        null|array|string $code = null,
        bool $with_states = false,
        bool $translate = true
    ): Collection {
        if (empty($this->countries)) {
            $this->countries = require __DIR__.'/../resources/data/countries.php';
        }
        if ($code && is_string($code)) {
            $code = [$code];
        }
        if ($code && is_array($code)) {
            $countries = [];
            foreach ($code as $country_code) {
                if (array_key_exists($country_code, $this->countries)) {
                    $copy = new \ArrayObject($this->countries[$country_code]);
                    $country = $copy->getArrayCopy();
                    unset($copy);
                    if ($translate && $this->locale) {
                        $country['name'] = $this->getTranslation($country['name'], 'country');
                    }
                    if ($with_states) {
                        $country['states'] = $this->getStates(
                            country_code: $country_code,
                            translate: $translate
                        );
                    }
                    $countries[$country_code] = (object) $country;
                }
            }

            return collect($countries);
        }

        return collect($this->countries);
    }

    public function getStates(
        string $country_code,
        bool $translate = true
    ): Collection {
        if (empty($this->states)) {
            $this->states = require __DIR__.'/../resources/data/states.php';
        }
        $states = $this->states[$country_code] ?? [];
        if ($translate && $this->locale) {
            $copy = new \ArrayObject($states);
            $states = $copy->getArrayCopy();
            unset($copy);
            foreach ($states as $key => $state) {
                $states[$key]['name'] = $this->getTranslation($state['name'], 'state');
            }
        }

        return collect($states);
    }

    private function getTranslation($key, $type)
    {
        if (file_exists(__DIR__.'/../resources/lang/'.$this->locale.'/'.$type.'.php')) {
            $translations = require __DIR__.'/../resources/lang/'.$this->locale.'/'.$type.'.php';
        } else {
            return $key;
        }

        return $translations[$key] ?? $key;
    }
}
