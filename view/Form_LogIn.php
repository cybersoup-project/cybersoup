{% extends "base.html" %} {% block main %}


<form class="row g-3 col-12 col-md-6 needs-validation" id="form" ACTION="" NAME="inserta" METHOD="POST"
            ENCTYPE="multipart/form-data">
            <div class="col-12 col-md-6">
                <label for="validationCustom01" class="form-label">Nombre Completo</label>
                <input type="text" class="form-control" name="nombre" placeholder="ej:Juan" required>
                <div class="invalid-feedback">
                    Porfavor intruzca su nombre.
                </div>
            </div>
            <div class="col-12 col-md-6">
                <label for="validationCustomUsername" class="form-label">Username</label>
                <div class="input-group has-validation">
                    <span class="input-group-text" id="usuario">@</span>
                    <input type="text" class="form-control" id="validationCustomUsername" name="usuario"
                        aria-describedby="inputGroupPrepend" placeholder="ej:Juanito123" required>
                    <div class="invalid-feedback">
                        Porfavor intruzca un nombre de usuario.
                    </div>
                </div>
            </div>
            <div class="col-12">
                <button class="btn btn-primary" NAME="insertar" type="submit">LogIn</button>
            </div>
</form>



{% endblock %}