@if($errors->any())
<div class="form-errors" role="alert" tabindex="-1" data-form-errors><strong>Revisemos estos detalles para poder guardar.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul><p>Los textos que escribiste siguen aquí. Si elegiste una imagen, vuelve a seleccionarla.</p></div>
@endif
