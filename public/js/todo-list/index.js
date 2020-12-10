function onCompletedCheckboxChange(todoId, element) {
  $.ajax({
    url: "/todos/toggleCompleted",
    type: "PUT",
    data: {
      todoId,
      completed: element.checked,
    },
    success: () => location.reload(),
  });
}

flatpickr(".date-picker", {
  enableTime: true,
  dateFormat: "Y-m-d H:i",
});

function setInputValueAndSubmitForm(inputSelector, value) {
  $(inputSelector).val(value).closest("form").submit();
}
