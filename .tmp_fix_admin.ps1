$p='admin.php'
$c=Get-Content -Raw $p
$repls = @(
    @('InstituciÃ³n','Institución'),
    @('AdministraciÃ³n','Administración'),
    @('AquÃ­','Aquí'),
    @('EspaÃ±a','España'),
    @('JapÃ³n','Japón'),
    @('MÃ©xico','México'),
    @('PerÃº','Perú'),
    @('HungrÃ­a','Hungría'),
    @('PaÃ­ses','Países'),
    @('BÃ©lgica','Bélgica'),
    @('SudÃ¡frica','Sudáfrica'),
    @('CanadÃ¡','Canadá'),
    @('pÃ¡gina','página'),
    @('GestiÃ³n','Gestión'),
    @('conservarÃ¡n','conservarán'),
    @('existÃ­a','existía'),
    @('estÃ¡','está'),
    @('crearÃ¡','creará'),
    @('eliminarÃ¡','eliminará'),
    @('invÃ¡lida','inválida'),
    @('dinÃ¡micamente','dinámicamente'),
    @('TelÃ©fono','Teléfono'),
    @('NÃºmero','Número'),
    @('botÃ³n','botón'),
    @('acciÃ³n','acción'),
    @('tÃ©rmino','término'),
    @('PaÃ­s','País'),
    @('conexiÃ³n','conexión'),
    @('Â¿','¿'),
    @('Â©','©')
)
foreach($r in $repls){ $c=$c.Replace($r[0],$r[1]) }
$c=$c.Replace(' fixed top-0 left-0 right-0 z-50',' sticky top-0 z-50')
$c=$c.Replace('pt-36 md:pt-28','pt-6 md:pt-8')
[IO.File]::WriteAllText((Resolve-Path $p),$c,[Text.UTF8Encoding]::new($false))
'OK'
