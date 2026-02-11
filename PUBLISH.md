# How to register this package on Packagist / Composer

Follow these steps so others can install the package with `composer require liqwiz/laravel-sso-client`.

---

## 1. Create a repository on GitHub (or GitLab)

- The repo should contain **only the package folder** contents (this `laravel-sso-client` directory), **not** the full monorepo root.
- **Option A:** Create a new repo and copy the contents of this folder into it, then push.
- **Option B:** Use **git subtree split** to turn the subfolder into a separate repo.

**Example (splitting the subfolder into its own repo):**

```bash
# From monorepo root (e.g. firedirect_hub)
cd /path/to/firedirect_hub
git subtree split -P packages/laravel-sso-client -b laravel-sso-client-pkg
mkdir -p ../laravel-sso-client-pub && cd ../laravel-sso-client-pub
git init
git pull ../firedirect_hub laravel-sso-client-pkg
git remote add origin https://github.com/YOUR_USERNAME/laravel-sso-client.git
git push -u origin main
```

Or do it manually: create a new repo (e.g. `laravel-sso-client`), copy the contents of `packages/laravel-sso-client` (composer.json, src/, config/, etc.) into it, then commit and push.

---

## 2. Update composer.json

- **Package name:** Must be unique on Packagist. If you use a different vendor (e.g. `yourcompany`), use `yourcompany/laravel-sso-client`. The current name is `liqwiz/laravel-sso-client` — keep it if you want to use the "liqwiz" namespace.
- **authors:** Use your real name and email (they are shown publicly on Packagist).
- **homepage / support (optional):** Add your GitHub repo URL.

Example (edit to match your details):

```json
"authors": [
    { "name": "Your Name", "email": "your@email.com" }
],
"homepage": "https://github.com/your-username/laravel-sso-client",
"support": {
    "issues": "https://github.com/your-username/laravel-sso-client/issues",
    "source": "https://github.com/your-username/laravel-sso-client"
}
```

---

## 3. Add the package on Packagist

1. Go to **Packagist:** https://packagist.org  
2. **Log in** (you can sign up / log in with GitHub).  
3. Click **Submit** (or "Submit package").  
4. Enter your **Repository URL**, e.g.:  
   - `https://github.com/your-username/laravel-sso-client`  
   - or `git@github.com:your-username/laravel-sso-client.git`  
5. Click **Check** — Packagist will scan the repo and show the detected `composer.json`.  
6. Click **Submit**.  
7. The package is now public. Anyone can install it with:  
   ```bash
   composer require liqwiz/laravel-sso-client
   ```

---

## 4. Auto-update (recommended)

To have Packagist update the package on every push:

1. On Packagist → your profile → **Show API Token** — copy the token.  
2. In your GitHub repo → **Settings** → **Webhooks** → **Add webhook**:  
   - **Payload URL:** `https://packagist.org/api/github?username=YOUR_PACKAGIST_USERNAME&apiToken=YOUR_TOKEN`  
   - **Content type:** `application/json`  
   - **Events:** "Just the push event"  
3. Save. After that, every push to the repo will trigger Packagist to refresh the package versions.

---

## 5. Create a version tag (release)

`composer require liqwiz/laravel-sso-client` will resolve to the **latest stable** version. For that you need at least one **version tag**:

```bash
cd /path/to/laravel-sso-client-repo
git tag -a v1.0.0 -m "Initial release"
git push origin v1.0.0
```

Packagist will treat this as version 1.0.0. For future releases, push tags like `v1.0.1`, `v1.1.0`, etc.

---

## Checklist

- [ ] Create a GitHub (or GitLab) repo with only the package code  
- [ ] Update `composer.json` (name, authors, and optionally homepage/support)  
- [ ] Log in at packagist.org and submit the repo URL  
- [ ] Set up the webhook (optional but recommended)  
- [ ] Push a first release tag, e.g. `v1.0.0`  

After that, the package can be installed in any Laravel project with:

```bash
composer require liqwiz/laravel-sso-client
```

(once the package is public on Packagist).
