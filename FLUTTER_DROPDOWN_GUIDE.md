# Guide Flutter - DropdownButton Simple pour les Projets

## ✅ Modifications API Effectuées

L'API `/api/projects` retourne maintenant un champ `display` pré-formaté pour simplifier l'affichage :

```json
{
  "code_projet": "DPSI-GUT-0046",
  "nom_projet": "Réhabiltation des locaux techniques de la Douane",
  "display": "DPSI-GUT-0046 - Réhabiltation des locaux techniques de la Douane",
  "opportunity_id": "006Vk000004iIa1IAE"
}
```

**Tous les champs retournent des chaînes vides `""` au lieu de `null`** ✅

---

## 📱 Exemple Flutter - DropdownButton Simple

### 1. Modèle de données

```dart
class Project {
  final String codeProjet;
  final String nomProjet;
  final String display;
  final String opportunityId;

  Project({
    required this.codeProjet,
    required this.nomProjet,
    required this.display,
    required this.opportunityId,
  });

  factory Project.fromJson(Map<String, dynamic> json) {
    return Project(
      codeProjet: json['code_projet'] ?? '',
      nomProjet: json['nom_projet'] ?? '',
      display: json['display'] ?? '',
      opportunityId: json['opportunity_id'] ?? '',
    );
  }
}
```

### 2. DropdownButton Simple

```dart
class MaintenanceForm extends StatefulWidget {
  @override
  _MaintenanceFormState createState() => _MaintenanceFormState();
}

class _MaintenanceFormState extends State<MaintenanceForm> {
  List<Project> projects = [];
  Project? selectedProject;
  String companyName = '';
  bool isLoadingCompany = false;

  @override
  void initState() {
    super.initState();
    loadProjects();
  }

  Future<void> loadProjects() async {
    try {
      final response = await http.get(
        Uri.parse('${AppConstants.baseUrl}/api/projects'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final List<dynamic> data = json.decode(response.body);
        setState(() {
          projects = data.map((json) => Project.fromJson(json)).toList();
        });
      }
    } catch (e) {
      print('Erreur chargement projets: $e');
    }
  }

  Future<void> loadCompanyName(String opportunityId) async {
    if (opportunityId.isEmpty) {
      setState(() => companyName = '');
      return;
    }

    setState(() => isLoadingCompany = true);

    try {
      final response = await http.get(
        Uri.parse('${AppConstants.baseUrl}/api/opportunities/$opportunityId'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        setState(() {
          companyName = data['account_name'] ?? '';
          isLoadingCompany = false;
        });
      }
    } catch (e) {
      print('Erreur chargement entreprise: $e');
      setState(() => isLoadingCompany = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // Dropdown pour sélectionner le projet
        DropdownButtonFormField<Project>(
          value: selectedProject,
          decoration: InputDecoration(
            labelText: 'Projet',
            border: OutlineInputBorder(),
          ),
          isExpanded: true,
          items: projects.map((project) {
            return DropdownMenuItem<Project>(
              value: project,
              child: Text(
                project.display,
                overflow: TextOverflow.ellipsis,
              ),
            );
          }).toList(),
          onChanged: (Project? newValue) {
            setState(() {
              selectedProject = newValue;
            });

            // Charger le nom de l'entreprise
            if (newValue != null) {
              loadCompanyName(newValue.opportunityId);
            }
          },
          validator: (value) {
            if (value == null) {
              return 'Veuillez sélectionner un projet';
            }
            return null;
          },
        ),

        SizedBox(height: 16),

        // Champ nom de l'entreprise (auto-rempli)
        TextFormField(
          decoration: InputDecoration(
            labelText: 'Nom de l\'entreprise',
            border: OutlineInputBorder(),
            suffixIcon: isLoadingCompany
              ? SizedBox(
                  width: 20,
                  height: 20,
                  child: CircularProgressIndicator(strokeWidth: 2),
                )
              : null,
          ),
          controller: TextEditingController(text: companyName),
          readOnly: true,
          enabled: false,
        ),
      ],
    );
  }
}
```

### 3. Version avec SearchableDropdown (optionnel)

Si vous voulez une recherche, utilisez le package `dropdown_search` :

```yaml
# pubspec.yaml
dependencies:
  dropdown_search: ^5.0.6
```

```dart
DropdownSearch<Project>(
  items: projects,
  itemAsString: (Project project) => project.display,
  dropdownDecoratorProps: DropDownDecoratorProps(
    dropdownSearchDecoration: InputDecoration(
      labelText: "Rechercher un projet",
      border: OutlineInputBorder(),
    ),
  ),
  popupProps: PopupProps.menu(
    showSearchBox: true,
    searchFieldProps: TextFieldProps(
      decoration: InputDecoration(
        hintText: "Tapez pour rechercher...",
        prefixIcon: Icon(Icons.search),
      ),
    ),
  ),
  onChanged: (Project? newValue) {
    setState(() {
      selectedProject = newValue;
    });
    if (newValue != null) {
      loadCompanyName(newValue.opportunityId);
    }
  },
)
```

---

## 🔄 Flux complet

1. **Chargement des projets** : `GET /api/projects`
2. **Affichage dans le dropdown** : Utiliser le champ `display`
3. **Sélection d'un projet** : Récupérer `opportunity_id`
4. **Si opportunity_id non vide** : `GET /api/opportunities/{opportunity_id}`
5. **Afficher account_name** : Dans le champ "Nom de l'entreprise"

---

## ⚠️ Points importants

1. **Tous les champs peuvent être vides** (`""`), gérez-les correctement
2. **Utilisez `?? ''`** pour les valeurs par défaut en Dart
3. **Vérifiez `opportunityId.isEmpty`** avant de charger l'entreprise
4. **Utilisez `TextEditingController`** pour afficher le nom de l'entreprise
5. **Marquez le champ entreprise comme `readOnly`** et `enabled: false`

---

## 🎯 Recommandation

Pour éviter les erreurs de cast, **utilisez toujours** :
- `json['field'] ?? ''` pour les String
- `json['field'] ?? 0` pour les int
- `json['field'] ?? false` pour les bool
- `json['field'] ?? []` pour les List

**Ne jamais faire** :
```dart
String value = json['field']; // ❌ Peut crasher si null
```

**Toujours faire** :
```dart
String value = json['field'] ?? ''; // ✅ Safe
```

---

## 📋 Checklist Migration

- [ ] Supprimer le widget de recherche complexe actuel
- [ ] Implémenter le `DropdownButtonFormField` simple
- [ ] Ajouter le modèle `Project` avec les champs corrects
- [ ] Implémenter `loadCompanyName()` quand un projet est sélectionné
- [ ] Tester avec un projet qui a un `opportunity_id`
- [ ] Tester avec un projet qui n'a PAS d'`opportunity_id` (chaîne vide)
- [ ] Vérifier que le champ entreprise se remplit automatiquement
- [ ] Faire de même pour le formulaire Survey

---

## 🚀 Prochaines étapes

Même logique à appliquer pour :
1. **Survey** : Recherche de projet + affichage entreprise
2. **Intervention UTE** : Recherche de projet + affichage entreprise

**Voulez-vous que je vous aide avec autre chose ?**
