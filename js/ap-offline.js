// 1. Initialize Dexie (The Local Database)
const db = new Dexie("LGU_Basco_Registry");
db.version(1).stores({
    dogs: "tag_serial, dog_name, fullname" // Indexing these for fast search
});

// 2. Function to Sync from Server to Phone
async function syncRegistry() {
    const btn = document.getElementById('syncBtn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Syncing...';

    try {
        const response = await fetch('sync_data.php');
        const data = await response.json();

        // Clear old local data and save new data
        await db.dogs.clear();
        await db.dogs.bulkAdd(data);

        alert("Sync Complete! " + data.length + " records saved offline.");
        btn.innerHTML = '<i class="fas fa-sync"></i> Update Registry';
    } catch (err) {
        alert("Sync Failed. Check your internet connection.");
        btn.innerHTML = '<i class="fas fa-sync"></i> Try Again';
    }
}

// 3. Function to Search Local Database (Offline)
async function lookupDog(serial) {
    const dog = await db.dogs.get(serial);
    const resultDiv = document.getElementById('result');
    
    if (dog) {
        resultDiv.classList.remove('d-none');
        resultDiv.innerHTML = `
            <div class="text-start">
                <h5 class="text-primary fw-bold">${dog.dog_name}</h5>
                <p class="mb-1"><strong>Owner:</strong> ${dog.fullname}</p>
                <p class="mb-1"><strong>Contact:</strong> ${dog.contact_number}</p>
                <p class="mb-1"><strong>Vax Date:</strong> ${dog.last_rabies_vax}</p>
                <hr>
                <button class="btn btn-danger w-100">LOG IMPOUND</button>
            </div>
        `;
    } else {
        resultDiv.innerHTML = `<div class="alert alert-danger">Tag Not Found in Local Registry</div>`;
    }
}