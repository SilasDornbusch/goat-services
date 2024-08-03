/**
 * Loggt den Benutzer aus, indem er auf die logout.php weiterleitet.
 */
function logout() {
    window.location.href = "logout.php";
}

/**
 * Sendet eine Anfrage, um das Benutzerkonto zu löschen, nachdem der Benutzer die Aktion bestätigt hat.
 */
function deleteAccount() {
    // Bestätigungsdialog anzeigen und überprüfen, ob der Benutzer die Löschung bestätigt hat
    if (confirm("Sind Sie sicher, dass Sie Ihren Account löschen möchten? Diese Aktion kann nicht rückgängig gemacht werden.")) {
        
        // Senden der Anfrage zum Löschen des Kontos
        fetch('delete_account.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ action: 'delete_account' }) // Der zu sendende Datenkörper im JSON-Format
        })
        .then(response => response.json()) // Die Antwort als JSON parsen
        .then(data => {
            // Überprüfen, ob das Löschen erfolgreich war
            if (data.success) {
                alert("Ihr Account wurde erfolgreich gelöscht.");
                window.location.href = 'login.php'; // Weiterleitung zur Login-Seite nach erfolgreicher Löschung
            } else {
                alert("Es gab ein Problem beim Löschen Ihres Accounts."); // Fehlermeldung anzeigen
            }
        })
        .catch(error => {
            console.error('Error:', error); // Fehler im Konsolenprotokoll ausgeben
        });
    }
}

/**
 * Zeigt eine Bestätigungsaufforderung zum Löschen des Benutzerkontos an.
 * @returns {boolean} Gibt true zurück, wenn der Benutzer die Löschung bestätigt, andernfalls false.
 */
function confirmDelete() {
    return confirm("Sind Sie sicher, dass Sie Ihren Account löschen möchten? Diese Aktion kann nicht rückgängig gemacht werden?");
}
