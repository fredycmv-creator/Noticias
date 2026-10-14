@if($errors->any())
<div class="error-summary" role="alert" tabindex="-1">
    <h2>Revisa los datos del formulario</h2>
    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif
