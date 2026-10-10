# GitHub setup and update workflow

The project is published at <https://github.com/ahbarcelon/counterpoint-pos-system>. The steps below remain as a reusable record of the safe workflow used and the commands for later updates.

1. Open PowerShell in the completed project directory:

   ```powershell
   Set-Location 'C:\path\to\pos-app'
   Get-ChildItem -Force
   ```

2. Check `.gitignore`, and confirm the real `.env`, runtime logs, session files, and uploads are ignored:

   ```powershell
   Get-Content .gitignore
   Test-Path .env
   git check-ignore .env writable/logs/example.log public/uploads/products/example.jpg
   ```

3. Initialize the local repository on `main`:

   ```powershell
   git init
   git branch -M main
   git status
   ```

4. Review the files, stage them, and create the first commit:

   ```powershell
   git add .
   git status
   git diff --cached --stat
   git commit -m "Build CodeIgniter POS system"
   ```

5. On github.com, choose **New repository**, select your visibility, and create a brand-new empty repository. Do not initialize it with a README, `.gitignore`, or license because those files already exist locally. Replace the placeholders below with your own GitHub username and repository name:

   ```powershell
   git remote add origin https://github.com/<YOUR_USERNAME>/<YOUR_REPOSITORY>.git
   git remote -v
   git push -u origin main
   ```

   Complete GitHub’s browser sign-in, Personal Access Token prompt, or configured Git Credential Manager step yourself. Do not paste credentials into this project or into chat.

6. Verify that GitHub shows `app`, `public`, `writable` placeholders, migrations, `composer.json`, `composer.lock` if generated, `Dockerfile`, README, and all `docs` files. Confirm `.env`, generated uploads, and secrets are absent.

7. For later changes:

   ```powershell
   git status
   git add .
   git commit -m "Describe the change"
   git push
   ```

The repository URL above is the real published URL. Keep `.env`, generated uploads, logs, and credentials out of every future commit.
