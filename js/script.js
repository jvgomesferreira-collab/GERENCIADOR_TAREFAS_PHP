// js/script.js

/**
 * Validação de Formulário antes do envio 
 * Garante que o campo 'Título da Tarefa' não está vazio.
 */
function validarFormulario() {
    const titulo = document.getElementById('titulo').value.trim();
    
    if (titulo === "") {
        alert("🚨 Por favor, preencha o Título da Tarefa.");
        return false; // Impede o envio do formulário
    }
    
    // Simulação de Mensagem Dinâmica (Interação Visual )
    // Neste caso, a mensagem é mostrada e o formulário é enviado.
    console.log("Tarefa validada com sucesso. Enviando...");
    
   
    return true; // Permite o envio do formulário
}

