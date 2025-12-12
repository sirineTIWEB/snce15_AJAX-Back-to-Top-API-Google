// Sélection des éléments
const form = document.getElementById('newsletterForm');
const messageDiv = document.getElementById('message');

// Fonction pour afficher un message

// text et type sont des paramètres qu'on fait passer à la fonction pour personnaliser le message affiché
function showMessage(text, type = 'success') {
    messageDiv.textContent = text;
    messageDiv.className = 'message ' + type;
    messageDiv.style.display = 'block';

    // Masquer après 5 secondes
    setTimeout(() => {
        messageDiv.style.display = 'none';
    }, 5000);
}

// Gestion de la soumission du formulaire

// on utilise 'async' pour pouvoir utiliser 'await' à l'intérieur de la fonction, 'await' afin d'attendre la résolution d'une promesse (comme une requête réseau) avant de continuer l'exécution du code
form.addEventListener('submit', async (e) => {
    e.preventDefault();

    // Créer un objet FormData à partir du formulaire
    const formData = new FormData(form);

    // Validation côté client
    const nom = formData.get('nom').trim();
    const email = formData.get('email').trim();

    if (nom.length < 2) {
        showMessage('Le nom doit contenir au moins 2 caractères.', 'error');
        return;
    }

    if (!email.includes('@')) {
        showMessage('Veuillez entrer une adresse email valide.', 'error');
        return;
    }

    // Désactiver le bouton pendant l'envoi
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Envoi en cours...';

    try {
        // Envoi AJAX au fichier PHP
        const response = await fetch('process_newsletter.php', {
            method: 'POST',
            body: formData
        });

        // Parser la réponse JSON
        const data = await response.json();

        if (data.success) {
            // Succès (code 201)
            showMessage(data.message, 'success');
            form.reset();
        } else {
            // Erreur (codes 400, 409, 500)
            showMessage(data.message, 'error');

            // Afficher les erreurs détaillées si disponibles
            if (data.errors && data.errors.length > 0) {
                const errorList = data.errors.join(', ');
                showMessage(errorList, 'error');
            }
        }

    } catch (error) {
        console.error('Erreur:', error);
        showMessage('Une erreur réseau est survenue. Veuillez réessayer.', 'error');
    } finally {
        // Réactiver le bouton
        submitBtn.disabled = false;
        submitBtn.textContent = "S'inscrire";
    }
});