<?php


$conn = new mysqli("localhost", "root", "", "todo_app");

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["greska" => "Konekcija nije uspela"]));
}

$conn->set_charset("utf8mb4");
header("Content-Type: application/json");

$metod = $_SERVER["REQUEST_METHOD"];

if ($metod === "GET") {

    // dohvati sve zadatke, najnoviji prvi
    $rezultat = $conn->query("SELECT * FROM zadaci ORDER BY kreiran DESC");
    $zadaci = [];
    while ($red = $rezultat->fetch_assoc()) {
        $zadaci[] = $red;
    }
    echo json_encode($zadaci);

} elseif ($metod === "POST") {

    // dodaj novi zadatak
    $podaci = json_decode(file_get_contents("php://input"), true);
    $tekst  = trim($podaci["tekst"] ?? "");

    if (empty($tekst)) {
        http_response_code(400);
        echo json_encode(["greska" => "Tekst je obavezan"]);
        exit;
    }

    // stiti od sql injection napada
    $stmt = $conn->prepare("INSERT INTO zadaci (tekst) VALUES (?)");
    $stmt->bind_param("s", $tekst);
    $stmt->execute();

    echo json_encode([
        "id"      => $conn->insert_id,
        "tekst"   => $tekst,
        "zavrsen" => 0
    ]);

} elseif ($metod === "PUT") {

    // promeni status zadatka
    $id = intval($_GET["id"] ?? 0);

    $stmt = $conn->prepare("SELECT zavrsen FROM zadaci WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $zadatak = $stmt->get_result()->fetch_assoc();

    if (!$zadatak) {
        http_response_code(404);
        echo json_encode(["greska" => "Zadatak nije pronadjen"]);
        exit;
    }

    // ako je 0 postavi na 1 i obrnuto
    $novi_status = $zadatak["zavrsen"] == 0 ? 1 : 0;

    $stmt = $conn->prepare("UPDATE zadaci SET zavrsen = ? WHERE id = ?");
    $stmt->bind_param("ii", $novi_status, $id);
    $stmt->execute();

    echo json_encode(["zavrsen" => $novi_status]);

} elseif ($metod === "DELETE") {

    // obrisi zadatak
    $id = intval($_GET["id"] ?? 0);

    $stmt = $conn->prepare("DELETE FROM zadaci WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo json_encode(["uspeh" => true]);

}

$conn->close();
?>
