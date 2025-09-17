document.getElementById("uploadForm").addEventListener("submit", async function(e) {
  e.preventDefault();

  const formData = new FormData(this);
  const status = document.getElementById("status");
  const preview = document.getElementById("preview");

  status.textContent = "Enviando...";
  preview.innerHTML = "";

  try {
    const response = await fetch("../backend/upload_api.php", {
      method: "POST",
      body: formData
    });

    const result = await response.json();

    if (result.success) {
      status.textContent = "Upload realizado com sucesso!";
      preview.innerHTML = `<img src="../backend/${result.url}" alt="Imagem enviada">`;
    } else {
      status.textContent = "Erro: " + result.error;
    }
  } catch (err) {
    status.textContent = "Erro de conexão: " + err.message;
  }
});
