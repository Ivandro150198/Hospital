# Hospital do Mal de Hansen de Cumura (HMH)

Site institucional do Hospital do Mal de Hansen de Cumura, Região de Biombo, Guiné-Bissau.

## Como correr localmente

1. Coloque o projeto em `C:\xampp\htdocs\hmh` (já no sítio com XAMPP).
2. Inicie Apache no painel do XAMPP.
3. Abra `http://localhost/hmh/`.

## Formulário de contacto

1. Copie `enviar-email.php.example` para `enviar-email.php` (se ainda não existir).
2. Edite `$destino` com o e-mail real do hospital.
3. Em XAMPP, as mensagens são também gravadas na pasta `mensagens/` (criada automaticamente).

## Estrutura

- `index.html` — página principal
- `style.css` — estilos
- `script.js` — interações
- `enviar-email.php` — backend do formulário (não versionado)
- `images/` — fotografias do hospital
