<?php

namespace App\Support;

class MobileLabels
{
    /** @var array<string, string> */
    private const EVENTS = [
        'page_view' => 'Vizualizare pagină',
        'page_leave' => 'Părăsire pagină',
        'banner_click' => 'Click pe banner',
        'product_view' => 'Vizualizare produs',
        'category_view' => 'Vizualizare categorie',
        'search' => 'Căutare',
        'filter_applied' => 'Filtru aplicat',
        'login_success' => 'Autentificare reușită',
        'sign_up_success' => 'Creare cont',
        'add_to_cart' => 'Adăugat în coș',
        'remove_from_cart' => 'Scos din coș',
        'cart_quantity_changed' => 'Cantitate în coș',
        'add_to_favorites' => 'Adăugat la favorite',
        'remove_from_favorites' => 'Scos de la favorite',
        'compare_add' => 'Adăugat la comparare',
        'compare_remove' => 'Scos din comparare',
        'checkout_started' => 'Începutul plății',
        'checkout_step' => 'Pas din plată',
        'cart_abandoned' => 'Coș părăsit',
        'checkout_completed' => 'Plată finalizată',
        'payment_completed' => 'Plată reușită',
        'payment_failed' => 'Plată eșuată',
        'order_completed' => 'Comandă finalizată',
        'map_open' => 'Deschidere hartă',
        'card_barcode_open' => 'Deschidere card (cod)',
        'notification_open' => 'Deschidere notificări',
        'notification_tap' => 'Apăsare notificare',
        'feedback_submitted' => 'Mesaj trimis',
        'request_product' => 'Cerere produs',
        'store_rating_native_request' => 'Cerere recenzie magazin',
        'discount_card_generate_start' => 'Generare card începută',
        'discount_card_generate_success' => 'Card generat',
        'discount_card_generate_error' => 'Generare card eșuată',
        'discount_card_verify_start' => 'Verificare card începută',
        'discount_card_verify_success' => 'Card adăugat după cod',
        'discount_card_verify_error' => 'Verificare card eșuată',
        'discount_card_verify_already_linked' => 'Card deja asociat',
        'discount_card_refresh_start' => 'Reîmprospătare carduri',
        'discount_card_refresh_success' => 'Carduri reîmprospătate',
        'discount_card_refresh_empty' => 'Fără carduri la reîmprospătare',
        'discount_card_refresh_error' => 'Reîmprospătare carduri eșuată',
        'discount_card_sync_start' => 'Sincronizare carduri',
        'discount_card_sync_success' => 'Carduri sincronizate',
        'discount_card_sync_empty' => 'Fără carduri la sincronizare',
        'discount_card_sync_error' => 'Sincronizare carduri eșuată',
        'discount_card_status_confirmed' => 'Status card confirmat',
        'card_generated' => 'Card generat',
        'card_created' => 'Card generat',
        'generate_card' => 'Card generat',
        'card_generate' => 'Card generat',
        'cards_generated' => 'Card generat',
        'card_generat' => 'Card generat',
        'generare_card' => 'Card generat',
        'wishlist_add' => 'Adăugat la favorite',
        'sign_up' => 'Creare cont',
        'login' => 'Autentificare',
        'screen_view' => 'Vizualizare ecran',
        'purchase' => 'Cumpărare',
        'begin_checkout' => 'Începutul plății',
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
