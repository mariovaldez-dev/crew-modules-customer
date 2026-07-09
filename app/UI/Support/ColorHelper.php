<?php

namespace App\UI\Support;

class ColorHelper
{
    /**
     * Lista de colores predefinidos para las etapas fenológicas.
     */
    public const STAGE_COLORS = [
        '#4A7C59', // Forest Green (Usuario)
        '#D97706', // Amber (Usuario)
        '#0EA5E9', // Sky Blue (Usuario)
        '#DC2626', // Red (Usuario)
        '#6B7280', // Gray (Usuario)
        '#8B5CF6', // Violet
        '#EC4899', // Pink
        '#F59E0B', // Orange
        '#10B981', // Emerald
        '#3B82F6', // Blue
        '#6366F1', // Indigo
        '#F43F5E', // Rose
        '#14B8A6', // Teal
        '#A855F7', // Purple
        '#71717A', // Zinc
    ];

    /**
     * Retorna un color basado en el índice de la etapa (cicla la lista).
     */
    public static function getStageColor(int $index): string
    {
        return self::STAGE_COLORS[$index % count(self::STAGE_COLORS)];
    }
}
