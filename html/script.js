document.addEventListener('DOMContentLoaded', function () {
    flatpickr('#calendario', {
      dateFormat: 'd/m/Y',  // Formato de la fecha (día/mes/año)
      locale: 'es',  // Idioma en español
      minDate: 'today',  // No se puede seleccionar una fecha anterior a hoy
      allowInput: false,  // Permite escribir manualmente la fecha
      onChange: function (selectedDates, dateStr, instance) {
        // Actualiza el valor del input con el formato correcto
        document.getElementById('calendario').value = dateStr;  // Aquí se guarda en 'd/m/Y'
      }
    });
  });
  