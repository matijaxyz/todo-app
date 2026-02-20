let zadaci = [];


document.addEventListener("DOMContentLoaded", ucitajZadatke);

// dohvati zadatke iz baze kada se stranica otvori
async function ucitajZadatke() {
    const odgovor = await fetch("api.php");
    zadaci = await odgovor.json();
    prikaziZadatke();
}

// dodaj novi zadatak
async function dodajZadatak() {
    const input = document.getElementById("inputZadatak");
    const tekst = input.value.trim();

    if (!tekst) return; // ne radi nista ako je prazno

    const odgovor = await fetch("api.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ tekst: tekst })
    });

    const noviZadatak = await odgovor.json();
    zadaci.unshift(noviZadatak); // dodaj na pocetak liste
    input.value = "";
    prikaziZadatke();
}

// oznaci zadatak kao zavrsen ili vrati na nezavrsen
async function toggleZadatak(id) {
    const odgovor = await fetch("api.php?id=" + id, { method: "PUT" });
    const rezultat = await odgovor.json();

    // pronadji zadatak u nizu i azuriraj mu status
    const zadatak = zadaci.find(z => z.id == id);
    zadatak.zavrsen = rezultat.zavrsen;

    prikaziZadatke();
}

// obrisi zadatak iz baze i ukloni ga sa stranice
async function obrisiZadatak(id) {
    await fetch("api.php?id=" + id, { method: "DELETE" });
    zadaci = zadaci.filter(z => z.id != id);
    prikaziZadatke();
}

// nacrta listu zadataka na stranici
function prikaziZadatke() {
    const lista = document.getElementById("lista");

    if (zadaci.length === 0) {
        lista.innerHTML = '<p class="prazno">Nema zadataka. Dodaj prvi!</p>';
        return;
    }

    lista.innerHTML = zadaci.map(z => `
        <div class="zadatak ${z.zavrsen == 1 ? "zavrsen" : ""}">
            <button class="checkbox" onclick="toggleZadatak(${z.id})">
                ${z.zavrsen == 1 ? "✓" : ""}
            </button>
            <span class="tekst">${z.tekst}</span>
            <button class="brisanje" onclick="obrisiZadatak(${z.id})">✕</button>
        </div>
    `).join("");
}

// dodaj zadatak pritiskom na Enter
document.addEventListener("DOMContentLoaded", () => {
    document.getElementById("inputZadatak").addEventListener("keydown", (e) => {
        if (e.key === "Enter") dodajZadatak();
    });
});
