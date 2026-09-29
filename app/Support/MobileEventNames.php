<?php

namespace App\Support;

/**
 * Numele reale din Volta_App (`lib/mobileAnalytics.ts` + `discountCardLogger.ts`).
 */
class MobileEventNames
{
    public static function canonical(string $name): string
    {
        $key = strtolower(trim($name));

        return match ($key) {
            'screen_view' => 'page_view',
            'view_item' => 'product_view',
            'begin_checkout' => 'checkout_started',
            'purchase', 'purchase_complete', 'order_complete' => 'order_completed',
            'login' => 'login_success',
            'sign_up' => 'sign_up_success',
            'wishlist_add' => 'add_to_favorites',
            'card_generated', 'card_created', 'generate_card', 'card_generate',
            'cards_generated', 'card_generat', 'generare_card' => 'discount_card_generate_success',
            default => $key,
        };
    }

    /** @return list<string> */
    public static function aliases(string $canonical): array
    {
        return match ($canonical) {
            'page_view' => ['page_view', 'screen_view'],
            'product_view' => ['product_view', 'view_item'],
            'order_completed' => ['order_completed', 'purchase', 'purchase_complete', 'order_complete'],
            'checkout_started' => ['checkout_started', 'begin_checkout'],
            'login_success' => ['login_success', 'login'],
            'sign_up_success' => ['sign_up_success', 'sign_up'],
            'add_to_favorites' => ['add_to_favorites', 'wishlist_add'],
            'discount_card_generate_success' => self::cardGeneratedEvents(),
            default => [$canonical],
        };
    }

    /** Card redus generat cu succes din Profil — nu barcode, nu sync, nu erori. */
    /** @return list<string> */
    public static function cardGeneratedEvents(): array
    {
        return [
            'discount_card_generate_success',
            'card_generated',
            'card_created',
            'generate_card',
            'card_generate',
            'cards_generated',
            'card_generat',
            'generare_card',
        ];
    }

    public static function isCardGenerated(string $name): bool
    {
        return in_array(strtolower(trim($name)), self::cardGeneratedEvents(), true);
    }

    public static function sqlSum(string $canonical, string $as): string
    {
        $list = implode(',', array_map(
            fn (string $name) => "'".str_replace("'", "''", $name)."'",
            self::aliases($canonical)
        ));

        return "SUM(CASE WHEN event_name IN ({$list}) THEN 1 ELSE 0 END) as {$as}";
    }

    /** @return list<string> */
    public static function chartSeries(): array
    {
        return [
            'page_view',
            'product_view',
            'search',
            'add_to_cart',
            'banner_click',
            'cart_abandoned',
            'order_completed',
            'discount_card_generate_success',
        ];
    }

    /** Toate numele brute care alimentează seriile din grafic. */
    /** @return list<string> */
    public static function chartEvents(): array
    {
        $names = [];
        foreach (self::chartSeries() as $series) {
            array_push($names, ...self::aliases($series));
        }

        return array_values(array_unique($names));
    }
}
