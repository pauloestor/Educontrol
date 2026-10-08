document.addEventListener("DOMContentLoaded", async () => {

    const profName = document.getElementById("profName");
    const profCode = document.getElementById("profCode");
    const profDepartment = document.getElementById("profDepartment");
    const dateInput = document.getElementById("attendanceDateInput");

    // data atual
    const today = new Date().toLocaleDateString("pt-PT");
    dateInput.value = today;

    try {

        const response = await fetch("phpprofessor/carregar_professor.php");
        const data = await response.json();

        if (!data.success) {
            console.error(data.message);
            return;
        }

        const prof = data.data;

        if (profName) profName.value = prof.nome || "";
        if (profCode) profCode.value = prof.numero_mecanografico || "";
        if (profDepartment) profDepartment.value = prof.disciplina || "Sem disciplina";

    } catch (error) {
        console.error("Erro ao carregar professor:", error);
    }

});

