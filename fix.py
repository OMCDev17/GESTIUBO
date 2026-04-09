# coding: utf-8
from pathlib import Path
path = Path('empleado.php')
text = path.read_text(encoding='utf-8')
repl = {
    'Logo de la InstituciÃƒÂƒÃ†Â’ÃƒÂ†Ã¢Â€Â™ÃƒÂƒÃ¢Â€Â ÃƒÂ¢Ã¢Â‚Â¬Ã¢Â„Â¢ÃƒÂƒÃ†Â’ÃƒÂ¢Ã¢Â‚Â¬Ã‚Â ÃƒÂƒÃ‚Â¢ÃƒÂ¢Ã¢Â€ÂšÃ‚Â¬ÃƒÂ¢Ã¢Â€ÂžÃ‚Â¢ÃƒÂƒÃ†Â’ÃƒÂ†Ã¢Â€Â™ÃƒÂƒÃ‚Â¢ÃƒÂ¢Ã¢Â€ÂšÃ‚Â¬ÃƒÂ…Ã‚Â¡ÃƒÂƒÃ†Â’ÃƒÂ¢Ã¢Â‚Â¬Ã…Â¡ÃƒÂƒÃ¢Â€ÂšÃƒÂ‚Ã‚Â³n': 'Logo de la Institución',
    'Ãƒâ€šÃ‚Â© 2026': '© 2026',
    'ÃƒÂ¢Ã‚â‚¬Ã‚â€œ': '—'
}
for k,v in repl.items():
    text = text.replace(k,v)
path.write_text(text, encoding='utf-8')
