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

function initializeFlatpickr(selector) {
  flatpickr(selector, {
    enableTime: true,
    dateFormat: "Y-m-d H:i",
  });
}

initializeFlatpickr(".date-picker");

function setInputValueAndSubmitForm(inputSelector, value) {
  $(inputSelector).val(value).closest("form").submit();
}

function addDeadlineToTodo(todoId) {
  $(`#todo_${todoId}_deadline`).append(
    `<input type="text" style="width: 150px;" onchange="saveDeadline(${todoId}, this.value)" class="form-control date-picker" placeholder="Deadline">`
  );

  initializeFlatpickr(".date-picker");
}

function saveDeadline(todoId, deadline) {
  $.ajax({
    url: `/todos/${todoId}/updateDeadline`,
    type: "PUT",
    data: {
      deadline,
    },
    success: () => location.reload(),
  });
}
