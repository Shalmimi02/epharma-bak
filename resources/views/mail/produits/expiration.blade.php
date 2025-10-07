<x-mail::message>
# Les produits dont la date d'expiration est inférieure à 30 jours

@foreach ($produits as $item)
- **{{$item->produit_libelle}}** ({{date('d/m/Y',strtotime($item->date_expiration))}})  
@endforeach

Cordialement,<br>
{{ config('app.name') }}
</x-mail::message>
