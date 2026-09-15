document.addEventListener("DOMContentLoaded", () => {
  
  // 1. ANIMACIÓN SCROLL REVEAL AL DESPLAZARSE
  const revealElements = document.querySelectorAll(".scroll-reveal");

  const revealOnScroll = () => {
    const windowHeight = window.innerHeight;
    revealElements.forEach((el) => {
      const elementTop = el.getBoundingClientRect().top;
      const elementVisible = 100;

      if (elementTop < windowHeight - elementVisible) {
        el.classList.add("visible");
      }
    });
  };

  window.addEventListener("scroll", revealOnScroll);
  revealOnScroll(); // Ejecución inicial

  // 2. CAMBIO DE CLASE ACTIVA EN EL NAVEGADOR
  const sections = document.querySelectorAll("section");
  const navLinks = document.querySelectorAll(".nav-links a");

  window.addEventListener("scroll", () => {
    let current = "";
    sections.forEach((section) => {
      const sectionTop = section.offsetTop;
      const sectionHeight = section.clientHeight;
      if (pageYOffset >= sectionTop - 200) {
        current = section.getAttribute("id");
      }
    });

    navLinks.forEach((link) => {
      link.classList.remove("active");
      if (link.getAttribute("href").includes(current)) {
        link.classList.add("active");
      }
    });
  });

  // 3. ENVÍO INTERACTIVO DEL FORMULARIO DE SOPORTE
  const supportForm = document.getElementById("supportForm");

  if (supportForm) {
    supportForm.addEventListener("submit", (e) => {
      e.preventDefault();

      const nombre = document.getElementById("nombre").value;
      const tipo = document.getElementById("tipo").value;

      // Mensaje de feedback animado
      alert(`¡Gracias, ${nombre}! Tu solicitud para servicio (${tipo}) ha sido recibida. Nos comunicaremos contigo pronto.`);

      supportForm.reset();
    });
  }
});