 <div class="container-login">
     <form method="post" class="body-div-from" id="form-login">
         <figure>
             <img
                 src="public/multimedia/log/001.png"
                 alt="logo de la sociedad venezolana de profesionales en medicina prehospitalaria" />
         </figure>

         <h2 class="title-login">Login</h2>

         <div class="div-input campo-icon">
             <div class="container-svg">
                 <svg class="icono-outline">
                     <use href="#icono-usuario"></use>
                 </svg>
             </div>
             <input
                 type="text"
                 name="usuario"
                 id="usuario"
                 placeholder=" "
                 required />

             <label for="usuario">Usuario</label>
         </div>

         <div class="div-input campo-icon">
             <div class="container-svg">
                 <svg class="icono-outline">
                     <use href="#icono-candado "></use>
                 </svg>
             </div>
             <input
                 type="password"
                 name="contrasena"
                 id="contrasena"
                 placeholder=" "
                 required />
             <label for="contrasena">Contraseña</label>
         </div>
         <input type="hidden" name="csrf_token" value="<?= $token ?? '' ?>">
         <button class="btn-summit" type="submit">
             <svg class="icono-outline">
                 <use href="#icono-login"></use>
             </svg>
             Login
         </button>
     </form>
 </div>

 <script src="public/js/login.js" defer></script>