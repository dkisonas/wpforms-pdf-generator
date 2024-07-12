<?php

namespace classes;

class ProductData
{
    public string $description = '';
    public string $category = '';
    public string $glassPackageType = '';
    public bool $isNarrowGlazingNeeded = false;
    public string $height = '';
    public string $width = '';
    public string $frameType = '';
    public bool $isTransportNeeded = false;
    public bool $hasGlassImitation = false;
    public bool $isOldGlassRemovalNeeded = false;
    public float $finalPrice = 0.0;
    public string $glassThickness = '';
    public string $glassStructure = '';
    public bool $isReplacementWorkNeeded = false;
    public float $basePrice = 0.0;
    public int $quantity = 0;
    public float $totalPrice = 0.0;

    public function getDescription(): string
    {
        $description = $this->height . "x" . $this->width . " " . $this->glassStructure . " " . $this->glassThickness;
        if (str_contains($this->frameType, 'Termo (TGI)')) {
            $description .= ' TGI';
        }
        if (str_contains($this->frameType, 'Standartinis aliuminis')) {
            $description .= ' ALU';
        }
        if ($this->isTransportNeeded) {
            $description .= ' TR';
        }
        if ($this->hasGlassImitation) {
            $description .= ' IS';
        }
        if ($this->isOldGlassRemovalNeeded) {
            $description .= ' UT';
        }
        if ($this->isReplacementWorkNeeded) {
            $description .= ' PD';
        }
        if ($this->isNarrowGlazingNeeded) {
            $description .= ' SS';
        }
        return $description;
    }
}