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
