# Hospital do Mal de Hansen de Cumura (HMH)

Site institucional do Hospital do Mal de Hansen de Cumura, Região de Biombo, Guiné-Bissau.

## Como correr localmente

1. Coloque o projecto em `C:\xampp\htdocs\hmh`.
2. Inicie Apache no XAMPP.
3. Abra `http://localhost/hmh/` (usa `index.php`).

## Painel SEO (admin)

1. Abra `http://localhost/hmh/admin/`
2. Login padrão:
   - Utilizador: `admin`
   - Password: `hmh-admin-2026`
3. Edite SEO, contactos e URLs e clique **Guardar e aplicar no site**.

**Importante:** altere a password em produção.

```bash
php -r "echo password_hash('sua-nova-password', PASSWORD_DEFAULT);"
```

Cole o hash em `config/auth.php`.

## Antes de publicar

1. Actualize a URL canónica no admin (ex.: `https://seudominio.org/`).
2. Actualize telefone, e-mail e WhatsApp no admin.
3. Em `enviar-email.php`, defina `$destino` com o e-mail real.
4. Altere a password do admin.

## Segurança

- `mensagens/` bloqueada via HTTP
- Admin com sessão, CSRF e rate-limit de login
- `data/seo.json` não é servido directamente
- Formulário com honeypot, rate-limit e consentimento

## Estrutura

- `index.php` — site (SEO dinâmico)
- `admin/` — painel de gestão SEO
- `data/seo.json` — dados editáveis
- `includes/seo.php` — leitura/escrita SEO
- `privacidade.html` — política de privacidade
