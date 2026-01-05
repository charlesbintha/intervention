# Récapitulatif API Mobile - Portail Intervention

**Date**: 11 Décembre 2025
**Status**: ✅ API Authentication fonctionnelle

---

## 🔐 Authentification

### ✅ POST /api/login
- **Status**: Fonctionnel
- **Credentials test**:
  - Email: `test@mobile.com`
  - Password: `Test123!`
- **Response**:
  ```json
  {
    "token": "11|yAhrVliXSwgnXyQd4zo0fAZpHRUuL7gKzrsP0LIA723bb5f9",
    "user": {
      "id": 3,
      "name": "Test Mobile",
      "email": "test@mobile.com",
      "role": "user",
      "is_active": true
    }
  }
  ```

### ✅ POST /api/logout
- **Status**: Fonctionnel
- **Headers**: `Authorization: Bearer {token}`
- **Response**: Message de succès

### ✅ GET /api/user
- **Status**: Fonctionnel
- **Headers**: `Authorization: Bearer {token}`
- **Response**: Données de l'utilisateur connecté

---

## 📋 APIs de Données (GET)

### ✅ GET /api/projects
- **Status**: Disponible
- **Description**: Liste complète des projets depuis l'API Oracle
- **Headers**: `Authorization: Bearer {token}`
- **Note**: Données en cache pendant 1h

### ✅ GET /api/employees
- **Status**: Disponible
- **Description**: Liste des employés GUT
- **Headers**: `Authorization: Bearer {token}`

### ✅ GET /api/opportunities
- **Status**: Disponible
- **Description**: Liste des opportunités Salesforce
- **Headers**: `Authorization: Bearer {token}`

### ✅ GET /api/opportunities/{id}
- **Status**: Disponible
- **Description**: Détails d'une opportunité spécifique
- **Headers**: `Authorization: Bearer {token}`

---

## 📝 Surveys (Fiches de Visites)

### ✅ GET /api/surveys
- **Status**: Fonctionnel
- **Description**: Liste des surveys de l'utilisateur (admin voit tout)
- **Headers**: `Authorization: Bearer {token}`

### ✅ POST /api/surveys
- **Status**: Fonctionnel
- **Headers**: `Authorization: Bearer {token}`
- **Body**:
  ```json
  {
    "opportunity_id": "string",
    "project_name": "string",
    "company_name": "string",
    "location": "string",
    "visit_datetime": "datetime",
    "contact_name": "string",
    "contact_function": "string",
    "contact_phone": "string",
    "contact_email": "email",
    "description": "text",
    "observations": "text",
    "recommendations": "text",
    "intervenants_gut": [
      {
        "nom": "string",
        "prenom": "string",
        "email": "email (optional)",
        "telephone": "string (optional)",
        "api_id": "int (optional)",
        "source": "api|manual"
      }
    ]
  }
  ```

---

## 🔧 Maintenances

### ✅ GET /api/maintenances
- **Status**: Fonctionnel
- **Description**: Liste des maintenances de l'utilisateur (admin voit tout)
- **Headers**: `Authorization: Bearer {token}`

### ✅ POST /api/maintenances
- **Status**: Fonctionnel
- **Headers**: `Authorization: Bearer {token}`
- **Body**:
  ```json
  {
    "project_name": "string",
    "company_name": "string",
    "location": "string",
    "contact_name": "string",
    "contact_function": "string",
    "contact_phone": "string",
    "contact_email": "email",
    "start_datetime": "datetime",
    "end_datetime": "datetime",
    "purpose": "text",
    "layout_content": "html (optional)",
    "status": "draft|pending|validated (optional)",
    "intervenants_gut": [
      {
        "nom": "string",
        "prenom": "string",
        "email": "email (optional)",
        "telephone": "string (optional)",
        "api_id": "int (optional)",
        "source": "api|manual"
      }
    ],
    "intervenants_rencontres": [
      {
        "nom": "string",
        "prenom": "string",
        "email": "email (optional)",
        "telephone": "string (optional)"
      }
    ]
  }
  ```

---

## ⚡ Interventions UTE

### ✅ GET /api/intervention-utes
- **Status**: Fonctionnel
- **Description**: Liste des interventions UTE de l'utilisateur (admin voit tout)
- **Headers**: `Authorization: Bearer {token}`

### ✅ POST /api/intervention-utes
- **Status**: Fonctionnel
- **Headers**: `Authorization: Bearer {token}`
- **Body**:
  ```json
  {
    "project_name": "string",
    "company_name": "string",
    "location": "string",
    "contact_name": "string",
    "contact_function": "string",
    "contact_phone": "string",
    "contact_email": "email",
    "start_datetime": "datetime",
    "end_datetime": "datetime",
    "nature_intervention": "string",
    "description": "text",
    "intervenants_gut": [
      {
        "nom": "string",
        "prenom": "string",
        "email": "email (optional)",
        "telephone": "string (optional)",
        "api_id": "int (optional)",
        "source": "api|manual"
      }
    ],
    "intervenants_rencontres": [
      {
        "nom": "string",
        "prenom": "string",
        "email": "email (optional)",
        "telephone": "string (optional)"
      }
    ]
  }
  ```

---

## ❌ Fonctionnalités MANQUANTES pour Mobile

### 1. **Détails individuels**
- ❌ GET /api/surveys/{id}
- ❌ GET /api/maintenances/{id}
- ❌ GET /api/intervention-utes/{id}

### 2. **Modification**
- ❌ PUT /api/surveys/{id}
- ❌ PUT /api/maintenances/{id}
- ❌ PUT /api/intervention-utes/{id}

### 3. **Suppression**
- ❌ DELETE /api/surveys/{id}
- ❌ DELETE /api/maintenances/{id}
- ❌ DELETE /api/intervention-utes/{id}

### 4. **Signature (Maintenances)**
- ❌ POST /api/maintenances/{id}/signature
- **Besoin**: Permettre la signature depuis le mobile

### 5. **PDF Generation**
- ❌ GET /api/surveys/{id}/pdf
- ❌ GET /api/maintenances/{id}/pdf
- ❌ GET /api/intervention-utes/{id}/pdf

### 6. **Mise en page (Maintenances)**
- ❌ PUT /api/maintenances/{id}/layout
- **Note**: Peut-être pas nécessaire sur mobile, mais à confirmer

### 7. **Upload d'images/fichiers**
- ❌ API pour uploader des photos/documents
- **Besoin**: Photos de chantier, signatures, documents

### 8. **Statuts et workflow**
- ❌ PUT /api/maintenances/{id}/status
- **Besoin**: Changer le statut (draft → pending → validated)

### 9. **Synchronisation offline**
- ❌ Mécanisme de gestion hors ligne
- **Besoin**: Créer des formulaires offline et sync plus tard

---

## 🔧 Recommandations d'implémentation

### Priorité HAUTE
1. **GET /show endpoints**: Permettre de voir les détails d'un enregistrement
2. **PUT /update endpoints**: Permettre la modification
3. **DELETE endpoints**: Permettre la suppression
4. **Upload d'images**: Essentiel pour les photos de chantier

### Priorité MOYENNE
5. **Signature API**: Permettre la signature depuis mobile
6. **PDF generation**: Générer et télécharger les PDFs
7. **Gestion des statuts**: Workflow de validation

### Priorité BASSE
8. **Synchronisation offline**: Feature avancée
9. **Mise en page sur mobile**: Probablement pas nécessaire

---

## 📱 Notes pour l'app mobile

### Headers requis
```
Accept: application/json
Content-Type: application/json
Authorization: Bearer {token}
```

### Gestion des erreurs
- **401**: Token expiré ou invalide → Rediriger vers login
- **403**: Compte désactivé
- **422**: Erreurs de validation
- **404**: Ressource non trouvée
- **500**: Erreur serveur

### Base URL
- **Développement**: `http://localhost:8000`
- **Production**: À définir

---

## ✅ Prochaines étapes

1. Implémenter les endpoints manquants prioritaires
2. Ajouter la gestion des images/fichiers
3. Tester tous les endpoints avec Postman/Insomnia
4. Créer une collection Postman pour l'équipe mobile
5. Documenter les codes d'erreur spécifiques
