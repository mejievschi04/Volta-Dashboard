<?php

namespace App\Support;

class MobileLabels
{
    /** @var array<string, string> */
    private const EVENTS = [
        'page_view' => 'Vizualizare pagină',
        'screen_view' => 'Vizualizare ecran',
        'product_view' => 'Vizualizare produs',
        'search' => 'Căutare',
        'add_to_cart' => 'Adăugat în coș',
        'remove_from_cart' => 'Scos din coș',
        'banner_click' => 'Click pe banner',
        'cart_abandoned' => 'Coș părăsit',
        'order_completed' => 'Comandă finalizată',
        'purchase' => 'Cumpărare',
        'login_success' => 'Autentificare reușită',
        'login' => 'Autentificare',
        'logout' => 'Deconectare',
        'sign_up' => 'Creare cont',
        'map_open' => 'Deschidere hartă',
        'checkout_started' => 'Începutul plății',
        'begin_checkout' => 'Începutul plății',
        'checkout_completed' => 'Plată finalizată',
        'checkout_step' => 'Pas din plată',
        'session_start' => 'Început vizită',
        'session_end' => 'Sfârșit vizită',
        'app_open' => 'Deschidere aplicație',
        'app_close' => 'Închidere aplicație',
        'push_open' => 'Deschidere notificare',
        'notification_open' => 'Deschidere notificare',
        'share' => 'Distribuire',
        'card_generated' => 'Card generat',
        'card_created' => 'Card generat',
        'generate_card' => 'Card generat',
        'card_generate' => 'Card generat',
        'cards_generated' => 'Card generat',
        'card_generat' => 'Card generat',
        'generare_card' => 'Card generat',
        'wishlist_add' => 'Adăugat la favorite',
        'filter' => 'Filtrare',
        'sort' => 'Sortare',
        'tap' => 'Apăsare',
        'click' => 'Click',
    ];

    public static function event(?string $name): string
    {
        $key = strtolower(trim((string) $name));
        if ($key === '') {
            return 'Necunoscut';
        }

        if (isset(self::EVENTS[$key])) {
            return self::EVENTS[$key];
        }

        $pretty = trim(str_replace(['_', '-'], ' ', $key));

        return mb_strtoupper(mb_substr($pretty, 0, 1, 'UTF-8'), 'UTF-8')
            .mb_substr($pretty, 1, null, 'UTF-8');
    }

    public static function status(?string $status): string
    {
        return match (strtolower(trim((string) $status))) {
            '', 'new' => 'Nou',
            'open' => 'Deschis',
            'closed' => 'Închis',
            'resolved' => 'Rezolvat',
            default => self::event($status),
        };
    }
}
