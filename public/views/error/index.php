 <div class="container-error">
     <figure>
         <img
             src="<?= $img ?? '' ?>"
             alt="logo de la sociedad venezolana de profesionales en medicina prehospitalaria" />
     </figure>

     <h2 class="title-error">Error <?= $codigo ?? '' ?></h2>
     <p><?= $mensaje ?? '' ?></p>
     <button class="btn-volver" type="button" onclick="window.history.go(-1)">
         <svg class="icono-outline">
             <use href="#icon-salir"></use>
         </svg>
         Volver
     </button>
 </div>