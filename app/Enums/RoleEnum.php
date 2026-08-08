<?php

namespace App\Enums;

enum RoleEnum: string
{
    case ADMIN = 'admin';
    case ACCOUNT = 'account';
    case STAY = 'stay';

    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Admin',
            self::ACCOUNT => 'Account',
            self::STAY => 'Resident',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::ADMIN => 'danger',
            self::ACCOUNT => 'warning',
            self::STAY => 'secondary',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::ADMIN => 'bi-shield-lock',
            self::ACCOUNT => 'bi-cash-coin',
            self::STAY => 'bi-person',
        };
    }

    public static function getAllRoles(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getRoleLabels(): array
    {
        $roles = [];
        foreach (self::cases() as $case) {
            $roles[$case->value] = $case->label();
        }
        return $roles;
    }

    public static function getRoleBadges(): array
    {
        $badges = [];
        foreach (self::cases() as $case) {
            $badges[$case->value] = $case->badge();
        }
        return $badges;
    }

    public static function getRoleIcons(): array
    {
        $icons = [];
        foreach (self::cases() as $case) {
            $icons[$case->value] = $case->icon();
        }
        return $icons;
    }
}
