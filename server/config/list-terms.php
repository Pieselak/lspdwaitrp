<?php
$web_name = "LSPD WaitRP";
$web_url = "lspdweb.pl";
$web_email = "xd@gmail.com";

return [
    'title' => 'Warunki korzystania z usługi',
    'subtitle' => 'Wersja z dnia 14 marca 2025 r.',
    'sections' => [
        [
            'title' => 'Wprowadzenie',
            'items' => [
                'Niniejszy dokument określa zasady korzystania z serwisu internetowego '.$web_name.' (dalej "Serwis"), dostępnego pod adresem '.$web_url.'.',
                'Prosimy o uważne zapoznanie się z treścią Zasad Korzystania z Usług przed rozpoczęciem korzystania z Serwisu.',
                'Korzystanie z Serwisu oznacza akceptację niniejszych Zasad.'
            ]
        ],
        [
            'title' => 'Słownik pojęć',
            'items' => [
                'Serwis – platforma internetowa '.$web_name.', dostępna pod adresem '.$web_url.'.',
                'Użytkownik – każda osoba fizyczna korzystająca z Serwisu.',
                'Discord – platforma komunikacyjna wykorzystywana do autoryzacji Użytkowników w Serwisie.',
                'OAuth – protokół wykorzystywany do bezpiecznej autoryzacji Użytkownika bez udostępniania hasła.',
                'Dane osobowe – informacje umożliwiające identyfikację Użytkownika, w tym nazwa użytkownika Discord, identyfikator użytkownika oraz adres e-mail.'
            ]
        ],
        [
            'title' => 'Warunki dostępu do Serwisu',
            'items' => [
                'Dostęp do Serwisu możliwy jest wyłącznie poprzez autoryzację z wykorzystaniem konta Discord.',
                'Korzystanie z Serwisu jest dobrowolne i bezpłatne.',
                'Użytkownik musi posiadać aktywne konto Discord oraz spełniać wymagania wiekowe określone w warunkach korzystania z platformy Discord.'
            ]
        ],
        [
            'title' => 'Proces rejestracji i logowania',
            'items' => [
                'Rejestracja oraz każdorazowe logowanie do Serwisu odbywa się wyłącznie za pomocą mechanizmu OAuth dostarczanego przez Discord.',
                'Podczas procesu autoryzacji Serwis uzyskuje następujące informacje z konta Discord Użytkownika: nazwa użytkownika, identyfikator użytkownika oraz adres e-mail.',
                'Serwis nie ma dostępu do hasła Użytkownika do konta Discord.',
                'Użytkownik może w dowolnym momencie wycofać autoryzację dla Serwisu poprzez ustawienia swojego konta Discord.'
            ]
        ],
        [
            'title' => 'Przetwarzanie i ochrona danych osobowych',
            'items' => [
                'Dane pozyskane w procesie autoryzacji (nazwa użytkownika Discord, identyfikator oraz adres e-mail) są niezbędne do świadczenia usług w ramach Serwisu.',
                'Serwis automatycznie zapisuje adresy IP Użytkowników podczas każdego logowania w celach bezpieczeństwa oraz wykrywania nadużyć.',
                'Adresy IP przechowywane są przez okres do 90 dni od chwili logowania.',
                'Administrator Serwisu wdrożył odpowiednie środki techniczne i organizacyjne zapewniające ochronę przetwarzanych danych osobowych.',
                'Użytkownik ma prawo dostępu do swoich danych, ich sprostowania, usunięcia, ograniczenia przetwarzania oraz prawo do przenoszenia danych.'
            ]
        ],
        [
            'title' => 'Prawa i obowiązki Użytkownika',
            'items' => [
                'Użytkownik zobowiązuje się do korzystania z Serwisu zgodnie z obowiązującym prawem, dobrymi obyczajami oraz niniejszymi Zasadami.',
                'Użytkownik ponosi pełną odpowiedzialność za wszystkie działania wykonane po zalogowaniu się do Serwisu z wykorzystaniem jego konta Discord.',
                'Użytkownikowi zabrania się podejmowania działań mogących zakłócić prawidłowe funkcjonowanie Serwisu, w tym prób nieautoryzowanego dostępu do systemu.',
                'Użytkownik ma obowiązek niezwłocznie powiadomić administratora Serwisu o wszelkich naruszeniach bezpieczeństwa związanych z jego kontem.'
            ]
        ],
        [
            'title' => 'Ograniczenia odpowiedzialności',
            'items' => [
                'Administrator Serwisu nie ponosi odpowiedzialności za przerwy w funkcjonowaniu Serwisu wynikające z przyczyn technicznych.',
                'Serwis nie odpowiada za działania podejmowane przez platformę Discord, w tym za ewentualne naruszenia bezpieczeństwa po stronie Discord.',
                'Administrator zastrzega sobie prawo do czasowego wyłączenia Serwisu w celu konserwacji, naprawy lub ulepszenia jego funkcjonalności.',
                'Serwis nie gwarantuje, że jego funkcjonalności będą dostępne w sposób nieprzerwany i wolny od błędów.'
            ]
        ],
        [
            'title' => 'Usunięcie konta i zakończenie korzystania z Serwisu',
            'items' => [
                'Użytkownik może w dowolnym momencie zażądać usunięcia swojego konta poprzez kontakt z administratorem Serwisu.',
                'Użytkownik może również zakończyć korzystanie z Serwisu poprzez cofnięcie autoryzacji dla aplikacji w ustawieniach swojego konta Discord.',
                'Po usunięciu konta, dane Użytkownika zostaną usunięte z Serwisu, z wyjątkiem adresów IP, które mogą być przechowywane w celach bezpieczeństwa przez okres do 90 dni.',
                'Administrator Serwisu zastrzega sobie prawo do zawieszenia lub usunięcia konta Użytkownika w przypadku naruszenia niniejszych Zasad.'
            ]
        ],
        [
            'title' => 'Zmiany Zasad Korzystania z Usług',
            'items' => [
                'Administrator Serwisu zastrzega sobie prawo do jednostronnej zmiany Zasad w dowolnym czasie.',
                'O planowanych zmianach Zasad Użytkownicy będą informowani z wyprzedzeniem co najmniej 14 dni poprzez informację na stronie głównej Serwisu.',
                'Brak sprzeciwu Użytkownika wobec nowych Zasad i kontynuacja korzystania z Serwisu oznacza akceptację wprowadzonych zmian.',
                'W przypadku braku akceptacji zmian, Użytkownik powinien zaprzestać korzystania z Serwisu.'
            ]
        ],
        [
            'title' => 'Postanowienia końcowe',
            'items' => [
                'Niniejsze Zasady podlegają prawu polskiemu.',
                'Wszelkie spory wynikające z niniejszych Zasad będą rozstrzygane przez sąd właściwy dla siedziby administratora Serwisu.',
                'Jeżeli jakiekolwiek postanowienie niniejszych Zasad zostanie uznane za nieważne, pozostałe postanowienia pozostają w mocy.',
                'W sprawach nieuregulowanych niniejszymi Zasadami zastosowanie mają odpowiednie przepisy prawa polskiego.'
            ]
        ],
        [
            'title' => 'Kontakt',
            'items' => [
                'W przypadku pytań lub wątpliwości związanych z niniejszymi Zasadami lub funkcjonowaniem Serwisu, prosimy o kontakt pod adresem e-mail: '.$web_email.'.',
                'Administrator Serwisu: '.$web_name.', [adres siedziby].'
            ]
        ]
    ],
];