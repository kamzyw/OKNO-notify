# OKNO-notify v1.0
Sprawdza, czy nie ma nowych ocen na Platformie Administracyjnej OKNO Politechniki Warszawskiej

## Wymagania 

- PHP >= 5.6
- cURL
- CRON


_skrypt korzysta z funkcji mail_

## Instrukcja

Należy w pliku config.php ustawić poprawne dane:


### hash 
zabezpiezpiecza niepowołane wywołanie skryptu

| Nazwa | Wartości | Opis | 
| ------ | ------ | ------ |
| enabled | true/false | Włączanie/wyłączanie hasha |
| value | dowolny ciąg znaków | Wartość hasha |

np. ustawnienie
```php
	'hash' => array(
		'enabled' => true,
		'value' => 'okno'
	),
```
spowoduje że skrypt będzie trzeba wywołać z parametrem ?hash=okno


### email

ustawienia e-maili

| Nazwa | Wartości | Opis | 
| ------ | ------ | ------ |
| to | adres e-mail | Na jaki adres e-mail skrypt ma wysłać powiadomienie |
| from | adres e-mail | Jaki adres e-mail ma zostać wpisany jako e-mail nadawcy |


### okno

ustawienia dotyczące platformy

| Nazwa | Wartości | Opis | 
| ------ | ------ | ------ |
| login | login | Login do Platformy Administracyjnej OKNO (https://red.okno.pw.edu.pl) |
| password | hasło | Hasło do Platformy Administracyjnej OKNO (https://red.okno.pw.edu.pl) |
| history_file | nazwa pliku | Nazwa pliku w jakim skrypt ma zapisać dane w celach porównywania danych |

### Uprawnienia plików i pierwsze uruchomienie

Skrypt musi mieć uprawnienia do tworzenia plików

Zaleca się, aby pierwsze wywołanie skryptu odbyło się ręcznie, a następnie dla pliku z danymi ustawić uprawnienia 640, aby plik z ocenami nie był dostępny publicznie.

Należy pamiętać że jeżeli został właczony hash w ustawieniach, aby poprawnie wywołać skrypt.


## Informacje

Nie zaleca się zbyt częstego uruchamiania skryptu ;)
Skrypt wykrywa zarówno nowe oceny, jak i zmiany w ocenach.

## Przykładowa treść e-maila z powiadomieniem
Tytuł:
```text
Wykryto nowe oceny [1]! - Platforma Administracyjna OKNO
```
Treść:
```text
Przedmiot: Prawo Gospodarcze (Edycja: 2016/2017, 4)
Ocena: 4.5 - Termin 1 (4 ECTS)
Wystawione: 2017-06-11 11:23:55 przez (imię i nazwisko wstawiającego)

Wysłano 20-06-2017 o godz. 00:32:17 przez OKNO-notify
```
