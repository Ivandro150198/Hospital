# Hospital do Mal de Hansen de Cumura (HMH)

Site institucional do Hospital do Mal de Hansen de Cumura, Região de Biombo, Guiné-Bissau.

> **Nota:** este site usa **PHP**. Não funciona no Vercel (nem em hosts só estáticos). Use hosting com **PHP 8+** e Apache (cPanel, Hostinger, etc.).

## Como correr localmente

1. Coloque o projecto em `C:\xampp\htdocs\hmh`.
2. Inicie Apache no XAMPP.
3. Abra `http://localhost/hmh/` (usa `index.php`).

## Publicar em hosting PHP (cPanel / similar)

### 1. Requisitos do servidor

- PHP **8.0+** (recomendado 8.1 ou 8.2)
- Extensões: `json`, `mbstring`, `fileinfo`
- Apache com `mod_rewrite` (normal em cPanel)
- Pasta com permissão de escrita para `data/` e `mensagens/`

### 2. Enviar os ficheiros

1. No cPanel, abra o **Gestor de ficheiros** (ou use FTP/SFTP).
2. Vá à pasta do site (ex.: `public_html/` ou `public_html/hmh/`).
3. Envie **todo** o projecto (incluindo `admin/`, `data/`, `includes/`, `images/`, etc.).
4. **Não** envie (ou apague no servidor se existirem):
   - `config/auth.php` com password fraca de desenvolvimento — crie uma nova no servidor
   - pasta `.git/` (opcional, mas recomendado não publicar)

Ficheiros sensíveis que **têm** de existir no servidor:

| Ficheiro | Como criar |
|----------|------------|
| `config/auth.php` | Copie de `config/auth.php.example` e defina user + hash |
| `enviar-email.php` | Copie de `enviar-email.php.example` e ajuste o e-mail |

### 3. Criar `config/auth.php`

No servidor (ou localmente e depois enviar):

```bash
php -r "echo password_hash('SUA-PASSWORD-FORTE', PASSWORD_DEFAULT);"
```

Conteúdo mínimo de `config/auth.php`:

```php
<?php
return [
    'username' => 'admin',
    'password_hash' => 'COLE_AQUI_O_HASH',
];
```

### 4. Criar `enviar-email.php`

1. Copie `enviar-email.php.example` → `enviar-email.php`.
2. Altere `$destino` para o e-mail real do hospital.
3. Confirme que a pasta `mensagens/` existe e tem permissão de escrita (chmod `755` ou `775`).

### 5. Permissões

No Gestor de ficheiros / FTP, garanta escrita em:

- `data/` (SEO, serviços, notícias, marcações)
- `data/bookings.json`
- `mensagens/`
- `images/` (se for carregar fotos pelo admin)

Exemplo típico: pastas `755`, ficheiros JSON `644` (ou `775`/`664` se o PHP correr como outro utilizador).

### 6. Página inicial

O hosting deve preferir **`index.php`**.

- Em cPanel: **Domínios → Document Root** aponta para a pasta do site.
- O ficheiro `.htaccess` do projecto já define `DirectoryIndex index.php index.html`.
- Se abrir e vir “Redirecionar…”, está a servir só `index.html` sem PHP — active PHP ou corrija a pasta raiz.

### 7. Configurar o site no admin

1. Abra `https://SEUDOMINIO/admin/`
2. Entre com o utilizador/password definidos em `config/auth.php`
3. Em **SEO**:
   - URL canónica = `https://SEUDOMINIO/`
   - telefone, e-mail, WhatsApp
4. Em **Serviços**: dias de consulta e limites diários
5. Guarde

### 8. Checklist rápido após publicar

- [ ] `https://SEUDOMINIO/` mostra o site (não a página “Redirecionar…”)
- [ ] `https://SEUDOMINIO/admin/` pede login
- [ ] Formulário de contacto / marcação envia sem erro
- [ ] Calendário mostra dias do serviço escolhido
- [ ] Password do admin já não é a de desenvolvimento
- [ ] `enviar-email.php` com e-mail real

### 9. Ligar o GitHub (opcional)

Se o hosting tiver **Git Version Control** no cPanel:

1. Clone `https://github.com/Ivandro150198/Hospital.git`
2. Branch: `main`
3. Depois de cada `git push`, faça **Pull** no cPanel  
4. Recrie/confirme no servidor `config/auth.php` e `enviar-email.php` (não vão no Git)

## Painel admin (local)

1. Abra `http://localhost/hmh/admin/`
2. Login padrão de desenvolvimento:
   - Utilizador: `admin`
   - Password: `hmh-admin-2026`
3. **Altere sempre a password em produção.**

## Segurança

- `mensagens/` bloqueada via HTTP
- Admin com sessão, CSRF e rate-limit de login
- `data/*.json` não é servido directamente
- Formulário com honeypot, rate-limit e consentimento
- `enviar-email.php` e `config/auth.php` estão no `.gitignore`

## Estrutura

- `index.php` — site público
- `admin/` — painel (SEO, serviços, notícias)
- `data/` — JSON editável (SEO, serviços, notícias, marcações)
- `disponibilidade.php` — API de vagas do calendário
- `enviar-email.php` — formulário de contacto / marcação
- `privacidade.html` — política de privacidade
