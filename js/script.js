// Atualiza ano no rodapé
const anoSpan = document.getElementById('anoAtual');
if (anoSpan) {
    anoSpan.textContent = new Date().getFullYear();
}

// Destaque do link ativo na navbar ao clicar
const navLinks = document.querySelectorAll('.nav-link[href^="#"]');

navLinks.forEach(link => {
    link.addEventListener('click', function () {
        navLinks.forEach(l => l.classList.remove('active'));
        this.classList.add('active');
    });
});

// Fade-in das seções ao rolar
const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('show');
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.2 });

document.querySelectorAll('.fade-section').forEach(sec => observer.observe(sec));

/*Splash Screen*/

document.addEventListener("DOMContentLoaded", () => {
  const splash = document.getElementById("splash");
  if (!splash) return;

  // trava scroll enquanto splash aparece
  const prevOverflow = document.body.style.overflow;
  document.body.style.overflow = "hidden";

  // tempo do splash (3 a 5 segundos)
  const SPLASH_TIME = 3500; // 3000 a 5000

  // permite "pular" clicando
  splash.addEventListener("click", hideSplash);

  // fecha automático
  const t = setTimeout(hideSplash, SPLASH_TIME);

  function hideSplash() {
    clearTimeout(t);
    splash.classList.add("hide");

    // depois da transição, remove de vez e libera scroll
    setTimeout(() => {
      splash.remove();
      document.body.style.overflow = prevOverflow || "";
    }, 500);
  }
});
