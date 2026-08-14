<?php

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" href="multimedia/index/001.ico?v=<?php echo time(); ?>">

    <title>SVPMPH | Órgano Oficial de la Sociedad Venezolana de Profesionales en Medicina Prehospitalaria</title>
    <meta name="description" content="Portal Oficial de la SVPMPH. Ente rector del gremio de Medicina Prehospitalaria en Venezuela. Certificación técnica, formación académica en El Tigre y registro nacional de paramédicos. Liderado por Noli Bucarito.">
    <meta name="keywords" content="SVPMPH oficial, Sociedad Venezolana de Profesionales en Medicina Prehospitalaria, gremio paramédicos Venezuela, certificación prehospitalaria oficial, Noli Bucarito, medicina de emergencias Venezuela, registro nacional de paramédicos, ley de medicina prehospitalaria, educación técnica en salud Venezuela">
    <meta name="author" content="SVPMPH - Noli Bucarito">

    <meta property="og:site_name" content="SVPMPH Oficial">
    <meta property="og:title" content="SVPMPH - Institución Rectora de la Medicina Prehospitalaria en Venezuela">
    <meta property="og:description" content="Acceda al registro de profesionales, programas de formación avalados y noticias gremiales de la Sociedad Venezolana de Profesionales en Medicina Prehospitalaria.">
    <meta property="og:url" content="https://svpmph.org/">
    <meta property="og:type" content="website">
    <meta property="og:image" content="multimedia/index/001.ico?v=1.2">
    <base href="/svpmph.version.2.0/">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="robots" content="index, follow">
    <meta http-equiv="content-language" content="es-VE">


    <link rel="stylesheet" href="public/css/style.css">


    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": "Sociedad Venezolana de Profesionales en Medicina Prehospitalaria (SVPMPH)",
            "alternateName": "SVPMPH",
            "url": "https://svpmph.org",
            "logo": "https://svpmph.org/multimedia/index/001.png",
            "founder": {
                "@type": "Person",
                "name": "Noli Bucarito"
            },
            "areaServed": "Venezuela",
            "description": "Organización oficial dedicada a agrupar, formar y certificar a los profesionales de la medicina prehospitalaria y técnicos en emergencias médicas en todo el territorio nacional.",
            "location": {
                "@type": "Place",
                "name": "Hospital General de El Tigre - Área de Docencia e Investigación",
                "address": {
                    "@type": "PostalAddress",
                    "addressLocality": "El Tigre",
                    "addressRegion": "Anzoátegui",
                    "addressCountry": "VE"
                }
            }
        }
    </script>


    <script>
        function siDisponible() {
            Swal.fire({
                icon: 'success',
                title: '¡Opción disponible!',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#5ab411',
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'views/cliente.php?for&registro';
                }
            });


        }

        function noDisponible() {
            Swal.fire({
                icon: 'info',
                title: '¡Opción no disponible!',
                text: 'Esta función aún no está disponible. Por favor, inténtalo más tarde.',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#5ab411',
            });
        }
    </script>

</head>

<body class="index">
    <svg width="0" height="0" style="position:absolute">
        <defs>
            <linearGradient id="gradient-medico" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#ffffff;stop-opacity:1" />
                <stop offset="100%" style="stop-color:#e4e4e4;stop-opacity:1" />
            </linearGradient>
        </defs>
    </svg>
    <header>
        <nav class="nav_index">
            <h1>Sociedad Venezolana de Profesionales en Medicina Prehospitalaria</h1>
            <button type="button" onclick="toggleAsideMenu()" id="menu">Menú</button>
        </nav>
    </header>
    <div class="div_article_aside">
        <?php

        require_once 'public/views/index/article_index.php';

        ?>

        <aside class="aside_index aside_left">
            <?php
            ob_start();
            ?>
            <div class="menu-header">
                <svg class="svg-menu-title" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                <h3>Menú de Navegación</h3>

            </div>
            <ul class="footer-list">
                <li>
                    <svg class="svg-formal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    </svg>
                    </a>
                    Inicio

                    <ul>
                        <li>
                            <a href="views/cliente.php?for&login">
                                <svg class="svg-formal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                </svg>
                                Académico
                            </a>
                        </li>

                        <li onclick="noDisponible()">
                            <a>
                                <svg class="svg-formal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" width="20" height="14" rx="2" y="7"></rect>
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                </svg>
                                Egresados
                            </a>
                        </li>
                    </ul>
                <li>
                    <a>
                        <svg class="svg-formal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                        </svg>
                        Registro
                    </a>
                    <ul>
                        <li>
                            <a href="views/cliente.php?for&registro">
                                <svg class="svg-formal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 10L12 5L2 10L12 15L22 10Z"></path>
                                    <path d="M6 12V17C6 17 7 20 12 20C17 20 18 17 18 17V12"></path>
                                </svg>
                                Estudiantes
                            </a>
                        </li>
                        <li onclick="noDisponible()">
                            <a>
                                <svg class="svg-formal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                </svg>
                                Egresados
                            </a>
                        </li>
                        <li>
                            <a href="views/cliente.php?for&curso">
                                <svg class="svg-formal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                    <line x1="8" y1="21" x2="16" y2="21"></line>
                                    <line x1="12" y1="17" x2="12" y2="21"></line>
                                </svg>
                                Cursos y Talleres
                            </a>
                        </li>

                    </ul>
                </li>
                <li onclick="noDisponible()">
                    <a>
                        <svg class="svg-formal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                        Nosotros
                    </a>
                </li>



                <li>

                    <svg class="svg-formal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l2.28-2.28a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                    </svg>
                    Contacto
                    <ul>
                        <li>
                            <a href="https://wa.me/584164837246?text=Hola%20me%20interesa%20conocer%20mas%20informacion%20sobre%20su%20organizacion" target="_blank">
                                <svg class="svg-formal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                                </svg>
                                Sede Administrativa
                            </a>
                        </li>

                        <li>
                            <a href="https://wa.me/584262320438?text=Hola%20me%20interesa%20conocer%20mas%20informacion%20sobre%20su%20organizacion" target="_blank">
                                <svg class="svg-formal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l2.28-2.28a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                                Atención al Ciudadano
                            </a>
                        </li>

                    </ul>

                </li>
            </ul>
            <?php
            $menu = ob_get_clean();
            echo $menu;
            ?>
        </aside>
    </div>
    <footer>
        <div class="footer-container">
            <div class="footer-col">
                <h3 class="logo-text">SVPMPH</h3>
                <p>
                    Sociedad Venezolana de Medicina PreHospitalaria.<br> Comprometidos con la excelencia en la atención de emergencias.
                </p>
            </div>

            <div class="footer-col">

                <?php
                echo $menu;
                ?>

            </div>

            <div class="footer-col">
                <h3>Contacto</h3>
                <p>bucanoli@gmail.com</p>
                <div class="social-icons">
                </div>

            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 SVPMPH - Todos los derechos reservados.</p>
            <h3>Ubicacion</h3>
            <p><a href="https://ul.waze.com/ul?place=ChIJd9Xd6yqCzY0Rlbx7VAQogIc&ll=8.89626550%2C-64.23989550&navigate=yes&utm_campaign=default&utm_source=waze_website&utm_medium=lm_share_location">Hospital General Dr Felipe Guevara Rojas de el Tigre Edo-Anzoategui</a></p>
        </div>
    </footer>
</body>

</html>