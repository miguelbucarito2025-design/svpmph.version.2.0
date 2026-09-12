 <div class="container-error">
     <figure>
         <img
             src="https://pub-ee0a2e2b71aa456ba391d9d7e17ff2e7.r2.dev/img/poster_2026-09-02-095902.png"
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