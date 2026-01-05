/**
 * Custom Confirmation Dialogs
 * Utilise SweetAlert2 avec des styles personnalisés
 */

/**
 * Affiche une confirmation personnalisée
 * @param {string} title - Le titre de la confirmation (en bleu)
 * @param {string} message - Le message de la confirmation (en gris)
 * @param {string} confirmButtonText - Texte du bouton de confirmation (orange)
 * @param {string} cancelButtonText - Texte du bouton d'annulation
 * @returns {Promise<boolean>} - true si confirmé, false si annulé
 */
function customConfirm(title, message, confirmButtonText = 'Confirmer', cancelButtonText = 'Annuler') {
    return Swal.fire({
        title: title,
        html: `<p style="color: #6b7280; font-size: 16px;">${message}</p>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f97316', // Orange
        cancelButtonColor: '#6b7280', // Gris
        confirmButtonText: confirmButtonText,
        cancelButtonText: cancelButtonText,
        customClass: {
            title: 'custom-confirm-title',
            popup: 'custom-confirm-popup'
        },
        didOpen: () => {
            // Applique la couleur bleue au titre
            const title = document.querySelector('.custom-confirm-title');
            if (title) {
                title.style.color = '#3b82f6'; // Bleu
            }
        }
    }).then((result) => {
        return result.isConfirmed;
    });
}

/**
 * Confirmation de suppression
 * @param {string} itemName - Nom de l'élément à supprimer
 * @returns {Promise<boolean>}
 */
function confirmDelete(itemName = 'cet élément') {
    return customConfirm(
        'Confirmation de suppression',
        `Êtes-vous sûr de vouloir supprimer ${itemName} ? Cette action est irréversible.`,
        'Supprimer',
        'Annuler'
    );
}

/**
 * Confirmation de validation
 * @param {string} itemName - Nom de l'élément à valider
 * @param {string} additionalInfo - Information supplémentaire
 * @returns {Promise<boolean>}
 */
function confirmValidation(itemName = 'cet élément', additionalInfo = 'Cette action est irréversible et empêchera toute modification ou suppression.') {
    return customConfirm(
        'Confirmation de validation',
        `Êtes-vous sûr de vouloir valider ${itemName} ? ${additionalInfo}`,
        'Valider',
        'Annuler'
    );
}

/**
 * Confirmation de toggle status
 * @param {string} action - Action à effectuer
 * @returns {Promise<boolean>}
 */
function confirmToggleStatus(action) {
    return customConfirm(
        'Confirmation de changement de statut',
        `Êtes-vous sûr de vouloir ${action} ?`,
        'Confirmer',
        'Annuler'
    );
}

/**
 * Confirmation de régénération de mot de passe
 * @returns {Promise<boolean>}
 */
function confirmPasswordReset() {
    return customConfirm(
        'Confirmation de régénération',
        'Êtes-vous sûr de vouloir régénérer le mot de passe de cet utilisateur ?',
        'Régénérer',
        'Annuler'
    );
}

/**
 * Soumet un formulaire après confirmation
 * @param {Event} event - L'événement de soumission
 * @param {string} confirmationType - Type de confirmation ('delete', 'validate', 'toggle', 'password')
 * @param {string} itemName - Nom de l'élément
 * @param {string} additionalInfo - Information supplémentaire
 */
async function handleFormSubmitWithConfirmation(event, confirmationType, itemName = '', additionalInfo = '') {
    event.preventDefault();

    let confirmed = false;

    switch (confirmationType) {
        case 'delete':
            confirmed = await confirmDelete(itemName);
            break;
        case 'validate':
            confirmed = await confirmValidation(itemName, additionalInfo);
            break;
        case 'toggle':
            confirmed = await confirmToggleStatus(itemName);
            break;
        case 'password':
            confirmed = await confirmPasswordReset();
            break;
        default:
            confirmed = await customConfirm('Confirmation', 'Êtes-vous sûr de vouloir continuer ?');
    }

    if (confirmed) {
        event.target.submit();
    }
}
