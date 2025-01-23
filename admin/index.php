<?php
// ----------------------------------------------------------------------------
// -------------------------------------- DEFENITION DES PARAMETRES NECESSAIRES --------------------------------------
// ----------------------------------------------------------------------------

//parametres de profondeur
$_SESSION["profondeurDossier"] = 1;

// Paramètres de la base de données
$host = 'localhost';
$dbname = 'ob';
$username = 'root';
$password = '';

// API TVDB
$tvdbApiKey = 'b28d6fe1-17c2-4664-837c-27237c6cf8b6';
$tvdbBaseUrl = 'https://api4.thetvdb.com/v4/';

$tokenLifeSpan = 30;
$token = [
    "token" => "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJhZ2UiOiIiLCJhcGlrZXkiOiJiMjhkNmZlMS0xN2MyLTQ2NjQtODM3Yy0yNzIzN2M2Y2Y4YjYiLCJjb21tdW5pdHlfc3VwcG9ydGVkIjpmYWxzZSwiZXhwIjoxNzM4Nzk4NTYxLCJnZW5kZXIiOiIiLCJoaXRzX3Blcl9kYXkiOjEwMDAwMDAwMCwiaGl0c19wZXJfbW9udGgiOjEwMDAwMDAwMCwiaWQiOiIyNzE0MDQxIiwiaXNfbW9kIjpmYWxzZSwiaXNfc3lzdGVtX2tleSI6ZmFsc2UsImlzX3RydXN0ZWQiOmZhbHNlLCJwaW4iOm51bGwsInJvbGVzIjpbXSwidGVuYW50IjoidHZkYiIsInV1aWQiOiIifQ.gRlOmq2xjf12SvNdmp0C_eqSh96KwA8QfdIR8o5dsveT0GuBf1Wj53lOXJmhXJmBz_g45ZSRplcmJ5nY5D0dMWxcGioQdjyJvxL9QN0cNmqUOwUPSo2QZE9Rw_4kYl6P6y1gwvzj1MMQxbbFOBLwDaZAmFCbaOOVnekV37lnQW6pVT4rtDbiiboZu6WYNLowkk479V2fDV-2dhY_Pw0-WvC7AoSlh9fKVMRQFZr--LESMEJTcNlFkXyke1uQVQrn1TWwtyDwv-aYd4JzVD7uGuBaBvkK-ZyMS_tuxVMYnnjoS8XaluhQB2PQZCMsyoQC0VN2y0_h8ZWWw2h-VcmewwAkzwnfwLlTCU_yY-7s9xV8aEiZlprFToKUWkOQNPXmniD66oW76g-BFweEZPGi0GmH6NLnplCOvK0I3Q0KWV64rBmhmZe_kIoXIRIBY41mBDJLYDeDpPR7rDhjPhcGJrUl-KRCATliO6wibh_byCR1q5tBia0SNHcZUUKwjB4ZpVERoZdkuiq0-e6HvDN4V-yNQXZGAAmeoQGZYKApEH7wAPbF9lcF1dgjB3bnKMuZXRr6nJK9B5soNAtIYwtp5H0j7E9Sem-HF4ApNvAIwj_0hx8uXHn69bcLGzML-evy8sGxpG0e32I3fuW9jq_69zq1rIv-3l2xsTI_kbfIBgk",
    "date" => "2025-01-06 13:36:07"
];

// -------------------------------------- fin des definitions --------------------------------------

// ----------------------------------------------------------------------------
// --------------------------------------   CONNEXION A LA BASE DE DONNEES --------------------------------------
// ----------------------------------------------------------------------------

$pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// -------------------------------------- fin de connexion a la base de donnees --------------------------------------



// ----------------------------------------------------------------------------
// --------------------------------------   GESTION DES TOKENS --------------------------------------
// ----------------------------------------------------------------------------

/**
 * Génère un token à partir de l'API TVDB.
 *
 * @param string $BaseUrl URL de base de l'API.
 * @param string $ApiKey Clé API pour l'authentification.
 * 
 * @return array|string Tableau associatif contenant le token et sa date de création, ou une chaîne vide en cas d'échec.
 */
function generateToken(string $BaseUrl, string $ApiKey): array|string{
    $url = $BaseUrl . 'login';
    $token = '';

    $postFields = ["apikey" => $ApiKey];

    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($postFields),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Accept: application/json"
        ]
        ]);

    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        echo "Erreur cURL : " . curl_error($curl);
        return $token;
    }

    $decodedResponse = json_decode($response, true);
    curl_close($curl);

    if (isset($decodedResponse['data']['token'])) {
        $token = [
            "token" => $decodedResponse['data']['token'],
            "date" => date("Y-m-d H:i:s")
        ];
        echo "Token : " . $token["token"] . "\n";
        echo "Date de création : " . $token["date"] . "\n";
        return $token;
    } else {
        echo "Erreur : Impossible de générer le token. Réponse API : " . $response;
        return null;
    }
}



/**
 * Méthode permettant de calculer le temps restant d'un token selon une durée de vie donnée.
 * 
 * @param array|string $token Tableau associatif contenant :
 *                     - "token" : string La clé du token.
 *                     - "date"  : string La date de création du token au format "Y-m-d H:i:s".
 * @param int $tokenLifeSpan La durée de vie du token en jours.
 * 
 * @return int Le temps de vie restant du token en jours. Retourne 0 si le token est expiré.
 * 
 * @throws InvalidArgumentException Si le tableau $token est invalide ou si la date est incorrecte.
 */
function timeRemaining(array|string $token, int $tokenLifeSpan): int {

    if (!isset($token["date"]) || !strtotime($token["date"])) {
        throw new InvalidArgumentException("La date du token est invalide ou manquante.");
    }

    $currentDate = round(strtotime(date("Y-m-d H:i:s")) / (60 * 60 * 24)); // En jours
    $tokenCreationDate = round(strtotime($token["date"]) / (60 * 60 * 24)); // En jours

    $remainingTime = $tokenLifeSpan - ($currentDate - $tokenCreationDate);

    return max(0, (int) $remainingTime); // Retourne au moins 0
}


//changement du token s'il s'approche de la date de fin de vie

$tokenTimeRemaining = timeRemaining($token, $tokenLifeSpan);

if($tokenTimeRemaining < $tokenLifeSpan/10){
    $token = generateToken($tvdbBaseUrl,$tvdbApiKey);
}

// -------------------------------------- fin de la gestion des tokens --------------------------------------



// ----------------------------------------------------------------------------
// -------------------------------------- BOITE A OUTILS --------------------------------------
// ----------------------------------------------------------------------------

/**
 * Initialise une session cURL avec des options prédéfinies pour effectuer une requête API.
 * 
 * @param string $url L'URL cible de la requête.
 * @param array|string $token Le token d'authentification, sous la forme d'un tableau associatif contenant :
 *                     - "token" : string Le jeton d'accès pour l'API.
 *                     - "date"  : date de generation du token. 
 * 
 * @return CurlHandle La ressource cURL initialisée avec les options configurées.
 * 
 * @throws TypeError Si les types des paramètres ne sont pas conformes.
 * @throws RuntimeException Si le token est fourni sous un format incorrect.
 */
function curlInitOpt(string $url, array|string $token):CurlHandle{
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type : application/json",
            "Accept : application/json",
            "Authorization : Bearer " . $token['token']
        ]
    ]);

    return $curl;
}


/**
 * Effectue un appel cURL générique pour récupérer des données depuis l'API.
 *
 * @param string $url L'URL de l'API.
 * @param array|string $token Le token d'autorisation.
 * @return array Les données JSON décodées depuis l'API.
 * @throws RuntimeException Si une erreur cURL se produit.
 */
function fetchDataFromApi(string $url, array|string $token) {
    $curl = curlInitOpt($url, $token);
    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        curl_close($curl);
        throw new RuntimeException("Erreur cURL : " . curl_error($curl));
    }
    curl_close($curl);

    return json_decode($response, true);
}

// -------------------------------------- fin de boite a outils --------------------------------------


// ----------------------------------------------------------------------------
// -------------------------------------- INSERTION DE FILM ET SERIES --------------------------------------
// ----------------------------------------------------------------------------


/**
 * Met à jour la table des genres dans la base de données en récupérant les données d'une API.
 *
 * Cette fonction effectue une requête cURL vers une API pour récupérer une liste de genres,
 * vérifie les genres déjà présents dans la base de données et insère uniquement les nouveaux genres.
 *
 * @param string $BaseUrl L'URL de base de l'API.
 * @param string|array $token Le token d'authentification pour accéder à l'API, 
 *                           
 * @param PDO $pdo Une instance de connexion à la base de données PDO.
 *
 * @return void Cette fonction ne retourne aucune valeur.
 *
 * @throws RuntimeException Si une erreur cURL se produit, si le code HTTP de l'API n'est pas 200,
 *                          ou si les données retournées par l'API sont mal formées.
 * @throws InvalidArgumentException Si un paramètre requis est manquant ou incorrect.
 */
function genreTableUpdate(string $BaseUrl, string|array $token, PDO $pdo): void {
    // ----- cURL pour récupérer tous les genres -----
    $genreUrl = $BaseUrl . "genres";

    $curl = curlInitOpt($genreUrl, $token);
    $response = curl_exec($curl);

    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if ($httpCode !== 200) {
        throw new RuntimeException("Erreur API : HTTP $httpCode reçu depuis l'URL $genreUrl.");
    }

    if (curl_errno($curl)) {
        curl_close($curl);
        throw new RuntimeException("Erreur cURL : " . curl_error($curl));
    }
    curl_close($curl);
    
    $genresData = json_decode($response, true);

    if (!isset($genresData["data"]) || !is_array($genresData["data"])) {
        throw new RuntimeException("Données des genres manquantes ou mal formées.");
    }
    // ----- Fin récupération cURL -----

    // Récupération des genres présents dans la base de données
    $stmt = $pdo->prepare("SELECT genreName FROM genre");
    $stmt->execute();

    $existingGenres = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Insertion des nouveaux genres
    foreach ($genresData["data"] as $genre) {
        if (!isset($genre["name"])) {
            continue; // Ignorer les genres mal formés
        }

        if (!in_array($genre["name"], $existingGenres, true)) {
            $stmt = $pdo->prepare("INSERT INTO genre (genreName) VALUES (:genreName)");
            $stmt->execute([
                ":genreName" => $genre["name"]
            ]);
        }
    }
}



/**
 * Vérifie si un personnage existe dans la base de données et l'insère si absent.
 *
 * Cette fonction utilise une requête cURL pour récupérer les données d'un personnage à partir d'une API,
 * vérifie si le personnage est déjà présent dans la base de données, et l'insère si ce n'est pas le cas.
 *
 * @param string $BaseUrl L'URL de base de l'API.
 * @param string|array $token Le token d'authentification pour accéder à l'API, 
 *                           
 * @param int $characterID L'identifiant unique du personnage à récupérer depuis l'API.
 * @param PDO $pdo Une instance de connexion à la base de données PDO.
 *
 * @return void Cette fonction ne retourne aucune valeur.
 *
 * @throws RuntimeException Si une erreur cURL se produit, si le code HTTP de l'API n'est pas 200,
 *                          ou si les données retournées par l'API sont mal formées.
 * @throws InvalidArgumentException Si un paramètre requis est manquant ou incorrect.
 */

 function characterInDB(string $BaseUrl, string|array $token, PDO $pdo, int $characterID): string {
    // ----- cURL pour récupérer les informations du personnage -----
    $peopleUrl = $BaseUrl . "people/" . $characterID;

    $curl = curlInitOpt($peopleUrl, $token);
    $characterResponse = curl_exec($curl);

    if (curl_errno($curl)) {
        throw new RuntimeException("Erreur cURL : " . curl_error($curl));
    }

    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ($httpCode !== 200) {
        throw new RuntimeException("Erreur API : HTTP $httpCode reçu depuis l'URL $peopleUrl.");
    }

    curl_close($curl);

    $characterData = json_decode($characterResponse, true);

    if (!isset($characterData["data"]["name"])) {
        throw new RuntimeException("Données du personnage manquantes ou mal formées.");
    }

    // Si le personnage n'existe pas, on l'insère
    $imageUrl = isset($characterData["data"]["image"]) ? $characterData["data"]["image"] : null;

    $stmt = $pdo->prepare("INSERT INTO characterdb (name, imageUrl) VALUES (:name, :imageUrl)");
    $stmt->execute([
        ":name" => $characterData["data"]["name"],
        ":imageUrl" => $imageUrl
    ]);

    return $characterData["data"]["name"];
}



/**
 * Récupère les informations d'une production via l'API et les insère dans la base de données si elles n'existent pas déjà.
 *
 * Cette fonction effectue une requête cURL pour récupérer les informations d'une production, puis vérifie si cette
 * production existe déjà dans la base de données. Si ce n'est pas le cas, elle insère le nom de la production dans la
 * table `production`.
 *
 * @param string $BaseUrl L'URL de base de l'API pour récupérer les informations de la production.
 * @param string|array $token Le token d'authentification utilisé pour l'API, soit sous forme de chaîne.
 * @param PDO $pdo L'instance PDO utilisée pour interagir avec la base de données.
 * @param int $production L'ID de la production à récupérer via l'API.
 *
 * @throws RuntimeException Si une erreur cURL se produit ou si l'API retourne un code HTTP différent de 200.
 * @throws RuntimeException Si les données récupérées de l'API sont mal formées ou manquantes.
 */
function productionInDB(string $BaseUrl, string|array $token, PDO $pdo, int $production): void {
    // ----- cURL pour récupérer les informations de la production ----- 
    $companyUrl = $BaseUrl . "companies/" . $production;

    $curl = curlInitOpt($companyUrl, $token);
    $productionResponse = curl_exec($curl);
    
    // Vérifier les erreurs cURL
    if (curl_errno($curl)) {
        throw new RuntimeException("Erreur cURL : " . curl_error($curl));
    }

    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ($httpCode !== 200) {
        throw new RuntimeException("Erreur API : HTTP $httpCode reçu depuis l'URL $companyUrl.");
    }

    curl_close($curl);

    // Décoder la réponse JSON
    $productionData = json_decode($productionResponse, true);

    // Vérifier que la réponse contient bien les données attendues
    if (!isset($productionData["data"]["name"])) {
        throw new RuntimeException("Données de la production manquantes ou mal formées.");
    }
    // ----- Fin récupération cURL ----- 

    // Récupération des productions existantes dans la base de données
    $stmt = $pdo->prepare("SELECT name FROM production");
    $stmt->execute();

    $existingProductions = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Vérification et insertion si la production est absente
    if (!in_array($productionData["data"]["name"], $existingProductions)) {
        $stmt = $pdo->prepare("INSERT INTO production (name) VALUES (:name)");
        $stmt->execute([
            ":name" => $productionData["data"]["name"]
        ]);
    }
}

//OPTIMISATION EN returnIdByName
/*
function returnCharacterID($pdo, string $characterName):int{
    $stmt = $pdo->prepare("SELECT characterID FROM characterdb WHERE name = :characterName)");
    $stmt->execute([
        ":characterName" => $characterName
    ]);

    $characterID = $stmt->fetchAll(PDO::FETCH_COLUMN);

    return $characterID[0];
}

function returnProductionID($pdo, string $productionName):int{
    $stmt = $pdo->prepare("SELECT productionID FROM production WHERE name = :productionName)");
    $stmt->execute([
        ":productionName" => $productionName
    ]);

    $productionID = $stmt->fetchAll(PDO::FETCH_COLUMN);

    return $productionID[0];
}

function returnGenreID($pdo, string $genre):int{
    $stmt = $pdo->prepare("SELECT genreID FROM genre WHERE genreName = :genre)");
    $stmt->execute([
        ":productionName" => $genre
    ]);

    $productionID = $stmt->fetchAll(PDO::FETCH_COLUMN);

    return $productionID[0];
}
*/

/**
 * Récupère l'ID d'un élément d'une table en fonction de son nom.
 *
 * Cette fonction effectue une requête SQL générique pour rechercher l'ID d'un élément dans une table donnée 
 * en fonction de son nom (ou d'un autre critère). Elle retourne l'ID associé à l'élément.
 *
 * @param string $BaseUrl L'URL de base de l'API (non utilisé ici mais peut être utile dans des versions futures).
 * @param PDO $pdo L'instance PDO pour accéder à la base de données.
 * @param string $table Le nom de la table dans laquelle rechercher l'élément.
 * @param string $column Le nom de la colonne à rechercher.
 * @param string $name La valeur de l'élément à rechercher dans la colonne.
 *
 * @return int|null L'ID de l'élément trouvé ou `null` si l'élément n'existe pas.
 *
 * @throws RuntimeException Si une erreur SQL se produit.
 */
function returnIDByName($pdo, string $table, string $column, string $name): ?int {
    $sql = "SELECT {$table}ID FROM {$table} WHERE {$column} = :name";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":name" => $name
    ]);

    $result = $stmt->fetchAll(PDO::FETCH_COLUMN);

    return (int)($result[0] ?? null);
}


/**
 * Importe les données d'un film dans la base de données en récupérant les informations du film depuis une API, ainsi que ses sociétés de production, genres et personnages.
 * Les détails du film, tels que le nom, la description, la date de sortie, la durée et l'affiche, sont insérés dans la table 'film'.
 * Les sociétés de production, genres et personnages associés au film sont également insérés dans les tables d'association correspondantes.
 * 
 * @param string $BaseUrl L'URL de base de l'API à partir de laquelle les données du film sont récupérées.
 * @param array|string $token Le jeton d'autorisation pour l'authentification de l'API.
 * @param PDO $pdo L'instance PDO utilisée pour les interactions avec la base de données.
 * @param int $movieID L'ID du film à importer.
 * 
 * @return bool Retourne true si le film et ses données associées (production, genres, personnages) ont été correctement insérés dans la base de données, false sinon.
 */
function importFilm(string $BaseUrl, array|string $token, $pdo, int $movieID): bool {
    try {
        // cURL pour récupérer les informations du film demandé -----
        $movieInfoUrl = $BaseUrl . "movies/" . $movieID . "/extended";
        $movieInformations = fetchDataFromApi($movieInfoUrl, $token);

        $lang1 = "fra"; // traduction des infos du film
        $lang2 = "eng"; // sinon en anglais

        // récupérations des informations du film
        $runtime = $movieInformations["data"]["runtime"];
        $releaseDate = $movieInformations["data"]["first_release"]["date"];
        $posterUrl = $movieInformations["data"]["image"];

        if (in_array($lang1, $movieInformations["data"]["nameTranslations"])) {

            // cURL pour récupérer les informations traduites du film demandé -----
            $movieTransUrl = $BaseUrl . "movies/" . $movieID . "/translations" . "/" . $lang1;
            $movieTranslate = fetchDataFromApi($movieTransUrl, $token);

            $name = $movieTranslate["data"]["name"];
            $description = $movieTranslate["data"]["overview"];

        } elseif (in_array($lang2, $movieInformations["data"]["nameTranslations"])) {
            // ----- cURL pour récupérer les informations traduites du film demandé -----
            $movieTransUrl = $BaseUrl . "movies/" . $movieID . "/translations" . "/" . $lang2;
            $movieTranslate = fetchDataFromApi($movieTransUrl, $token);

            $name = $movieInformations["data"]["name"];
            $description = $movieTranslate["data"]["overview"];

        } else { // attribution du nom et description si la traduction française et anglaise ne sont pas disponibles
            $name = $movieInformations["data"]["name"];
            $description = "description non disponible";
        }

        // Vérification si le film existe déjà dans la base de données
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM film WHERE name = :name");
        $stmt->execute([":name" => $name]);
        $exists = $stmt->fetchColumn();

        if ($exists > 0) {
            // Si le film existe déjà, on ne fait pas l'insertion
            return false;
        }

        // Insertion du film
        $stmt = $pdo->prepare("INSERT INTO film(name, description, releaseDate, runtime, posterUrl) 
            VALUES (:name, :description, :releaseDate, :runtime, :posterUrl)");

        $stmt->execute([
            ":name" => $name,
            ":description" => $description,
            ":releaseDate" => $releaseDate,
            ":runtime" => $runtime,
            ":posterUrl" => $posterUrl
        ]);

        $movieIDdb = $pdo->lastInsertId();

        // Insertion des productions
        foreach ($movieInformations["data"]["companies"]["production"] as $prodCompany) {
            if (is_null($prodCompany["name"])) {
                continue; // Si la production est null, passer à la valeur suivante
            }

            productionInDB($BaseUrl, $token, $pdo, $prodCompany["id"]);

            // Vérification si l'association existe déjà
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM productioncontentassociation WHERE pk_ContentID = :contentID AND pk_ProductionID = :productionID");
            $stmt->execute([
                ":contentID" => $movieIDdb,
                ":productionID" => returnIDByName($pdo, "production", "name", $prodCompany["name"])
            ]);
            $exists = $stmt->fetchColumn();

            if ($exists == 0) {
                // Si l'association n'existe pas, on insère
                $stmt = $pdo->prepare("INSERT INTO productioncontentassociation (pk_ContentID, pk_ContentType, pk_ProductionID) VALUES (:contentID, :ContentType, :productionID)");
                $stmt->execute([
                    ":contentID" => $movieIDdb,
                    ":ContentType" => "film",
                    ":productionID" => returnIDByName($pdo, "production", "name", $prodCompany["name"])
                ]);
            }
        }

        // Insertion des genres
        foreach ($movieInformations["data"]["genres"] as $genre) {
            if (is_null($genre["name"])) {
                continue; // Si le genre est null, passer à la valeur suivante
            }

            // Vérification si l'association existe déjà
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM genecontentassociation WHERE pk_ContentID = :contentID AND pk_GenreID = :genreID AND pk_ContentType = :contentType");
            $stmt->execute([
                ":contentID" => $movieIDdb,
                ":genreID" => returnIDByName($pdo, "genre", "genreName", $genre["name"]),
                ":contentType" => "film"
            ]);
            $exists = $stmt->fetchColumn();

            if ($exists == 0) {
                // Si l'association n'existe pas, on insère
                $stmt = $pdo->prepare("INSERT INTO genecontentassociation (pk_ContentID, pk_GenreID, pk_ContentType) VALUES (:contentID, :genreID, :contentType)");
                $stmt->execute([
                    ":contentID" => $movieIDdb,
                    ":genreID" => returnIDByName($pdo, "genre", "genreName", $genre["name"]),
                    ":contentType" => "film"
                ]);
            }
        }

        // Insertion des personnages
        foreach ($movieInformations["data"]["characters"] as $character) {
            $characterName = characterInDB($BaseUrl, $token, $pdo, $character["peopleId"]);
            // Si l'association n'existe pas, on insère
            $stmt = $pdo->prepare("INSERT INTO charactercontentassociation (pk_CharacterID, pk_ContentID, characterType, pk_ContentType) VALUES (:characterID, :contentID, :characterType, :ContentType)");
            $stmt->execute([
                ":characterID" => returnIDByName($pdo, "characterdb", "name", $characterName),
                ":contentID" => $movieIDdb,
                ":characterType" => $character["peopleType"],
                ":ContentType" => "film"
            ]);
        }

        return true;

    } catch (RuntimeException $e) {
        echo "Erreur lors de l'importation du film : " . $e->getMessage();
        return false;
    }
}


function importSeries(string $BaseUrl, array|string $token, $pdo, int $seriesID){
    try{
        // cURL pour récupérer les informations du film demandé -----
        $seriesInfoUrl = $BaseUrl . "series/" . $seriesID . "/extended";
        $seriesInformations = fetchDataFromApi($seriesInfoUrl, $token);

        if($seriesInformations["status"] == "failure"){
            return false;
        }
        $lang1 = "fra"; // traduction des infos du film
        $lang2 = "eng"; // sinon en anglais

        // récupérations des informations du film
        $year = $seriesInformations["data"]["year"];
        $status = $seriesInformations["status"];
        $releaseDate = DateTime::createFromFormat('Y-m-d', datetime: (int)$year . '-01-01');
        $releaseDateString = $releaseDate->format('Y-m-d');
        $posterUrl = $seriesInformations["data"]["image"];

        if (in_array($lang1, $seriesInformations["data"]["nameTranslations"])) {

            // cURL pour récupérer les informations traduites du series demandé -----
            $seriesTransUrl = $BaseUrl . "series/" . $seriesID . "/translations" . "/" . $lang1;
            $seriesTranslate = fetchDataFromApi($seriesTransUrl, $token);

            $name = $seriesTranslate["data"]["name"];
            $description = $seriesTranslate["data"]["overview"];

        } elseif (in_array($lang2, $seriesInformations["data"]["nameTranslations"])) {
            // ----- cURL pour récupérer les informations traduites du series demandé -----
            $seriesTransUrl = $BaseUrl . "series/" . $seriesID . "/translations" . "/" . $lang2;
            $seriesTranslate = fetchDataFromApi($seriesTransUrl, $token);

            $name = $seriesInformations["data"]["name"];
            $description = $seriesTranslate["data"]["overview"];

        } else { // attribution du nom et description si la traduction française et anglaise ne sont pas disponibles
            $name = $seriesInformations["data"]["name"];
            $description = "description non disponible";
        }

        // Vérification si la series existe déjà dans la base de données
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM series WHERE name = :name");
        $stmt->execute([":name" => $name]);
        $exists = $stmt->fetchColumn();

        if ($exists > 0) {
            // Si la series existe déjà, on ne fait pas l'insertion
            return false;
        }

        // Insertion de la series
        $stmt = $pdo->prepare("INSERT INTO series(name, description, releaseDate, posterUrl,status) 
            VALUES (:name, :description, :releaseDate, :posterUrl, :status)");

        $stmt->execute([
            ":name" => $name,
            ":description" => $description,
            ":releaseDate" => $releaseDateString,
            ":posterUrl" => $posterUrl,
            ":status" => $status
        ]);

        $seriesIDdb = $pdo->lastInsertId();

        // Insertion des productions
        foreach ($seriesInformations["data"]["companies"] as $prodCompany) {
            if($prodCompany["companyType"]["companyTypeName"] == "Production Company"){
                if (is_null($prodCompany["name"])) {
                    continue; // Si la production est null, passer à la valeur suivante
                }

                productionInDB($BaseUrl, $token, $pdo, $prodCompany["id"]);

                // Vérification si l'association existe déjà
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM productioncontentassociation WHERE pk_ContentID = :contentID AND pk_ProductionID = :productionID AND pk_ContentType = :contentType");
                $stmt->execute([
                    ":contentID" => $seriesIDdb,
                    ":productionID" => returnIDByName($pdo, "production", "name", $prodCompany["name"]),
                    ":contentType" => "series"
                ]);
                $exists = $stmt->fetchColumn();

                if ($exists == 0) {
                    // Si l'association n'existe pas, on insère
                    $stmt = $pdo->prepare("INSERT INTO productioncontentassociation (pk_ContentID, pk_ContentType, pk_ProductionID) VALUES (:contentID, :ContentType, :productionID)");
                    $stmt->execute([
                        ":contentID" => $seriesIDdb,
                        ":ContentType" => "series",
                        ":productionID" => returnIDByName($pdo, "production", "name", $prodCompany["name"])
                    ]);
                }
            }

            // Insertion des genres
            foreach ($seriesInformations["data"]["genres"] as $genre) {
                if (is_null($genre["name"])) {
                    continue; // Si le genre est null, passer à la valeur suivante
                }

                // Vérification si l'association existe déjà
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM genecontentassociation WHERE pk_ContentID = :contentID AND pk_GenreID = :genreID AND pk_ContentType = :contentType");
                $stmt->execute([
                    ":contentID" => $seriesIDdb,
                    ":genreID" => returnIDByName($pdo, "genre", "genreName", $genre["name"]),
                    ":contentType" => "series"
                ]);
                $exists = $stmt->fetchColumn();

                if ($exists == 0) {
                    // Si l'association n'existe pas, on insère
                    $stmt = $pdo->prepare("INSERT INTO genecontentassociation (pk_ContentID, pk_GenreID, pk_ContentType) VALUES (:contentID, :genreID, :contentType)");
                    $stmt->execute([
                        ":contentID" => $seriesIDdb,
                        ":genreID" => returnIDByName($pdo, "genre", "genreName", $genre["name"]),
                        ":contentType" => "series"
                    ]);
                }
            }

            // Insertion des personnages
            foreach ($seriesInformations["data"]["characters"] as $character) {
                $characterName = characterInDB($BaseUrl, $token, $pdo, $character["peopleId"]);

                // Si l'association n'existe pas, on insère
                $stmt = $pdo->prepare("INSERT INTO charactercontentassociation (pk_CharacterID, pk_ContentID, characterType, pk_ContentType) VALUES (:characterID, :contentID, :characterType, :ContentType)");
                $stmt->execute([
                    ":characterID" => returnIDByName($pdo, "characterdb", "name", $characterName),
                    ":contentID" => $seriesIDdb,
                    ":characterType" => $character["peopleType"],
                    ":ContentType" => "series"
                ]);
            }



        }


    }catch (RuntimeException $e) {
        echo "Erreur lors de l'importation de la series : " . $e->getMessage();
        return false;
    }
}

// -------------------------------------- fin d insertion de table et films et series --------------------------------------

/* Définir la plage des identifiants des séries à importer  -- tentatif echoue
$debutId = 1;
$finId = 50;

// Boucler sur chaque identifiant dans la plage
for ($idSerie = $debutId; $idSerie <= $finId; $idSerie++) {
    // Appeler la fonction importSeries pour l'identifiant actuel
    try {
        if(importSeries($tvdbBaseUrl, $token, $pdo, $idSerie)){
            echo "Série avec l'ID $idSerie importée avec succès.<br>";
        }elseif(importFilm($tvdbBaseUrl, $token, $pdo, $idSerie)){
            echo "Film avec l'ID $idSerie importée avec succès.<br>";
        }
    } catch (Exception $e) {
        echo "Échec de l'importation de la série ou film avec l'ID $idSerie : " . $e->getMessage() . "<br>";
    }
}
*/

$seriesIDList = [253323,421260,81189,360115,79168,369060,355567,368207];
$filmIDList = [562,362,1713,1375,6389,349133,1057,3689,1092,105596];

// Importer les series
foreach ($seriesIDList as $seriesID) {
    try {
        importSeries($tvdbBaseUrl, $token, $pdo, $seriesID);
        echo "Série avec l'ID $seriesID importée avec succès.<br>";
    } catch (Exception $e) {
        echo "Échec de l'importation de la série avec l'ID $seriesID : " . $e->getMessage() . "<br>";
    }
}

// Importer les films
foreach ($filmIDList as $filmID) {
    try {
        importFilm($tvdbBaseUrl, $token, $pdo, $filmID);
        echo "Film avec l'ID $filmID importé avec succès.<br>";
    } catch (Exception $e) {
        echo "Échec de l'importation du film avec l'ID $filmID : " . $e->getMessage() . "<br>";
    }
}




?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importation TVDB</title>
    <link rel="stylesheet" href="style.css">
<body>
<header>
        <nav class="header-nav">
            <a href="index.php"><img src="img/obLogo.png" alt="Logo OB"></a>
            <a href="index.php">Quittez le mode administrateur</a>
        </nav>
    </header>

    <main>
        <section class="tvdb-import">
            <h1>Importation de Films et Séries depuis TVDB</h1>

            <!-- Barre de recherche -->
            <div class="search-bar">
                <input type="text" id="searchInput" placeholder="Recherchez un film ou une série..." />
                <button id="searchButton">Rechercher</button>
            </div>

            <!-- Liste des contenus affichés -->
            <form id="importForm" action="importHandler.php" method="POST">
                <div class="content-list">
                    <!-- Les films et séries seront affichés ici dynamiquement -->
                </div>
                <button type="submit" class="import-button">Importer les éléments sélectionnés</button>
            </form>
        </section>
    </main>

    <?php include "pagesOutils/footer.php"?>

</body>
</html>
