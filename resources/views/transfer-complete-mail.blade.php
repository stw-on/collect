Eine neue Dateiübertragung ist eingetroffen.

Typ: {{ $transfer->template->name }}
Transfer-ID: {{ $transfer->id }}

Dateien abrufen: {{ config('app.url') . "/download/{$transfer->id}/{$transfer->access_token}" }}
Schlüssel: {{ $key }}

Bitte löschen Sie diese E-Mail, nachdem Sie die Dateien abgerufen haben.
