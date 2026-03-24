<!DOCTYPE html>
<html class="light" lang="es">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Email Enviado - Lab Portal</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Argentum+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&display=swap" rel="stylesheet"/>
<style>
    body { font-family: 'Argentum Sans', sans-serif; }
</style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col font-display">
<div class="flex h-full min-h-screen w-full flex-col bg-slate-50 overflow-x-hidden">
    <div class="flex flex-col items-center pt-10 px-6">
        <div class="max-w-[360px] mb-6">
            <img alt="Logo universidad" class="w-full h-auto object-contain" src="imagenes/instituto-biorganica-agonzalez-original.png"/>
        </div>
    </div>
    <main class="flex-1 flex items-center justify-center p-6">
        <div class="flex flex-col max-w-[480px] w-full bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
            <div class="flex flex-col items-center mb-8">
                <div class="w-full aspect-video bg-lab-accent/5 rounded-xl mb-8 flex items-center justify-center overflow-hidden border border-lab-accent/10">
                    <span class="material-symbols-outlined text-lab-accent text-7xl select-none" style="font-variation-settings: 'FILL' 1, 'wght' 700;">mail</span>
                </div>
                <h2 class="text-slate-900 tracking-tight text-2xl md:text-3xl font-bold leading-tight text-center mb-2">
                    Correo enviado con Ã©xito
                </h2>
                <p class="text-slate-500 text-lg font-medium leading-normal text-center">
                    Hemos enviado un correo de recuperaciÃ³n a la direcciÃ³n indicada.
                </p>
            </div>
            <div class="text-center mb-8">
                <p class="text-slate-500 text-sm">
                    Si no lo ves en unos minutos, revisa tu carpeta de spam o correo no deseado.
                </p>
                <p class="text-slate-500 text-sm mt-4">
                    SerÃ¡s redirigido al inicio de sesiÃ³n en <span id="countdown" class="font-bold text-lab-accent">8</span> segundos.
                </p>
            </div>
            <div class="flex flex-col gap-3">
                <a href="Loggin.php" class="flex w-full items-center justify-center h-12 px-5 bg-lab-accent text-white text-base font-bold rounded-xl hover:bg-lab-accentHover transition-colors">
                    Volver a iniciar sesiÃ³n
                </a>
            </div>
        </div>
    </main>
</div>
<script>
let seconds = 8;
const countdownEl = document.getElementById('countdown');
const timer = setInterval(() => {
    seconds -= 1;
    if (countdownEl) countdownEl.textContent = seconds;
    if (seconds <= 0) {
        clearInterval(timer);
        window.location.href = 'Loggin.php';
    }
}, 1000);
</script>
</body>
</html>

