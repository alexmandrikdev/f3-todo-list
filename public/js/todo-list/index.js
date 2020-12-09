function onCheckboxChecked(todoId, element) {
  $.ajax({
    url: "/todos/toggleCompleted",
    type: "PUT",
    data: {
      todoId,
      completed: element.checked,
    },
    // success: (res) => console.log(res),
  });
}

flatpickr(".date-picker", {
  enableTime: true,
  dateFormat: "Y-m-d H:i",
});
