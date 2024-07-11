<?php

namespace classes;

class ProductData
{
    public string $description = '';
    public string $category = '';
    public string $glassPackageType = '';
    public string $narrowGlazing = '';
    public string $height = '';
    public string $width = '';
    public string $frame = '';
    public string $transport = '';
    public string $glassImitation = '';
    public string $oldGlassRemoval = '';
    public float $finalPrice = 0.0;
    public string $glassThickness = '';
    public string $glassStructure = '';
    public string $replacementWork = '';
    public float $basePrice = 0.0;
    public int $quantity = 0;
    public float $totalPrice = 0.0;

    public function getDescription(): string
    {
        $description = $this->height . "x" . $this->width . " " . $this->glassStructure . " " . $this->glassThickness;
        if (str_contains($this->frame, 'Termo (TGI)')) {
            $description .= ' TGI';
        }
        return $description;
    }
}