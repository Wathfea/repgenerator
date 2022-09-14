<?php

namespace Pentacom\Repgenerator\Domain\Pattern\Services;

use Illuminate\Support\Str;

/**
 * Transform all names for proper name handling
 *
 */
class RepgeneratorNameTransformerService
{
    /**
     * Model name should be ucfirst,no space,no special chars,singular E.g. Dog
     *
     * @var string
     */
    public string $modelName;

    /**
     * Lower case, plural version of $modelName E.g. dogs
     *
     * @var string
     */
    public string $modelNamePluralLowerCase;

    /**
     * Upper case, plural version of $modelName E.g. Dogs
     *
     * @var string
     */
    public string $modelNamePluralUcfirst;

    /**
     * Lower case, singular version of $modelName E.g. dog
     *
     * @var string
     */
    public string $modelNameSingularLowerCase;

    /**
     * Upper case, singular version of $modelName E.g. Dog
     *
     * @var string
     */
    public string $modelNameSingularUcfirst;

    /**
     * Relation name should be no space,no special chars  E.g. something or somethingElse
     *
     * @var string
     */
    public string $relationName;

    /**
     * Should be start with lower case, remove space and start capital  the next word, singular E.g. dogOwner
     *
     * @var string
     */
    public string $relationMethodNameSingular;

    /**
     * Should be start with lower case, remove space and start capital  the next word, plural E.g. dogOwners
     *
     * @var string
     */
    public string $relationMethodNamePlural;

    /**
     * @return string
     */
    public function getModelName(): string
    {
        return $this->modelName;
    }

    /**
     * @param  string  $modelName
     */
    public function setModelName(string $modelName): void
    {
        $this->modelName = $this->removeSpecialChars($this->removeSpaces(Str::ucfirst(Str::studly(Str::singular($modelName)))));
        $this->setModelNameSingularLowerCase();
        $this->setModelNamePluralLowerCase();
        $this->setModelNameSingularUcfirst();
        $this->setModelNamePluralUcfirst();
    }

    /**
     * @param  string  $string
     * @return array|string|null
     */
    private function removeSpecialChars(string $string): array|string|null
    {
        return preg_replace('/[^A-Za-z\d]/', '', $string);
    }

    /**
     * @param  string  $string
     * @return array|string
     */
    private function removeSpaces(string $string): array|string
    {
        return str_replace(' ', '', $string);
    }

    /**
     * @return string
     */
    public function getModelNamePluralLowerCase(): string
    {
        return $this->modelNamePluralLowerCase;
    }

    /**
     * @return string
     */
    public function getModelNamePluralUcfirst(): string
    {
        return $this->modelNamePluralUcfirst;
    }


    /**
     * @return void
     */
    public function setModelNamePluralLowerCase(): void
    {
        $this->modelNamePluralLowerCase = Str::plural(Str::lower($this->getModelName()));
    }

    /**
     * @return void
     */
    public function setModelNamePluralUcfirst(): void
    {
        $this->modelNamePluralUcfirst = Str::ucfirst(Str::plural($this->getModelName()));
    }

    /**
     * @return string
     */
    public function getModelNameSingularLowerCase(): string
    {
        return $this->modelNameSingularLowerCase;
    }

    /**
     * @return string
     */
    public function getModelNameSingularUcfirst(): string
    {
        return $this->modelNameSingularUcfirst;
    }


    /**
     * @return void
     */
    public function setModelNameSingularLowerCase(): void
    {
        $this->modelNameSingularLowerCase = Str::lower($this->getModelName());;
    }

    /**
     * @return void
     */
    public function setModelNameSingularUcfirst(): void
    {
        $this->modelNameSingularUcfirst = Str::ucfirst($this->getModelName());;
    }

    /**
     * @return string
     */
    public function getRelationMethodNamePlural(): string
    {
        return $this->relationMethodNamePlural;
    }

    /**
     * @return void
     */
    public function setRelationMethodNamePlural(): void
    {
        $this->relationMethodNamePlural = Str::plural($this->getRelationName());
    }

    /**
     * @return string
     */
    public function getRelationMethodNameSingular(): string
    {
        return $this->relationMethodNameSingular;
    }

    /**
     * @return void
     */
    public function setRelationMethodNameSingular(): void
    {
        $this->relationMethodNameSingular =  Str::singular($this->getRelationName());;
    }

    /**
     * @return string
     */
    public function getRelationName(): string
    {
        return $this->relationName;
    }

    /**
     * @param  string  $relationName
     */
    public function setRelationName(string $relationName): void
    {
        $this->relationName = $this->removeSpecialChars(Str::lcfirst(Str::studly($relationName)));
        $this->setRelationMethodNamePlural();
        $this->setRelationMethodNameSingular();
    }
}
