function onCompletedCheckboxChange(todoId, element) {
  $.ajax({
    url: "/todos/toggleCompleted",
    type: "PUT",
    data: {
      todoId,
      completed: element.checked,
    },
    success: (res) => {
      // console.log(JSON.parse(res));

      location.reload();
    },
  });
}

function initializeFlatpickr(selector, additionalOptions = {}) {
  return flatpickr(selector, {
    enableTime: true,
    dateFormat: "Y-m-d H:i:S",
    time_24hr: true,
    ...additionalOptions,
  });
}

initializeFlatpickr(".date-picker");

function setInputValueAndSubmitForm(inputSelector, value) {
  $(inputSelector).val(value).closest("form").submit();
}

function addDeadlineToTodo(todoId) {
  let deadline = now();

  $(`#todo_${todoId}_deadline`).append(deadline);

  saveDeadline(todoId, deadline);
}

let tmpDatepickerExists = false;

/**
 * Remove the existing datepicker.
 * Set the element's innerHTML to a datepicker.
 * Set the datepicker's defaultDate and onClose options. And open the datepicker
 *
 * @param {int} todoId
 * @param {string} deadline
 * @param {object} element
 */
function createTempDatepicker(todoId, deadline, element) {
  if ($("#tmp-date-picker").length) {
    removeTempDatepicker();
  }

  element.innerHTML = `<input type="text" id="tmp-date-picker" style="width: 150px;" class="form-control date-picker">`;

  tmpDatepickerExists = true;

  const datepicker = initializeFlatpickr("#tmp-date-picker", {
    defaultDate: deadline,
    onClose: (selectedDates, dateStr) => {
      if (deadline !== dateStr) {
        saveDeadline(todoId, dateStr);
      }
    },
  });

  setTimeout(() => datepicker.open(), 100);
}

/**
 * Remove the temporary datepicker
 */
function removeTempDatepicker() {
  const tmpDatepicker = $("#tmp-date-picker");

  let deadline = tmpDatepicker.val();

  tmpDatepicker.closest(".deadline").html(deadline);
}

function saveDeadline(todoId, deadline) {
  $.ajax({
    url: `/todos/${todoId}/updateDeadline`,
    type: "PUT",
    data: {
      deadline,
    },
    success: (res) => {
      // console.log(JSON.parse(res));

      location.reload();
    },
  });
}

/**
 * Remove the temporary datepicker if clicked outside
 */
$(document).click(({ target }) => {
  if (
    target.id !== "tmp-date-picker" &&
    !target.classList.contains("deadline") &&
    !$(target).closest(".flatpickr-calendar").length &&
    tmpDatepickerExists
  ) {
    removeTempDatepicker();
  }
});

let visibleTodoTextareaId = null;

function showTodoTextarea(todoId) {
  $(`#todo-${todoId}-todo`).addClass("d-none");

  const todoTextarea = $(`#todo-${todoId}-todo-textarea`);

  todoTextarea.removeClass("d-none");

  autosize.update(todoTextarea);

  setTimeout(() => (visibleTodoTextareaId = todoId), 100);
}

function hideTodoTextarea(todoId) {
  $(`#todo-${todoId}-todo`).removeClass("d-none");

  $(`#todo-${todoId}-todo-textarea`).addClass("d-none");

  visibleTodoTextareaId = null;
}

function saveTodo(todoId, todo) {
  $.ajax({
    url: `/todos/${todoId}/updateTodo`,
    type: "PUT",
    data: {
      todo,
    },
    success: (res) => {
      console.log(JSON.parse(res));

      location.reload();
    },
  });
}

$(document).click(({ target }) => {
  if (!target.classList.contains("todo-textarea") && visibleTodoTextareaId) {
    hideTodoTextarea(visibleTodoTextareaId);
  }
});

function now() {
  let today = new Date();

  let day = today.getDate();

  let month = today.getMonth() + 1;

  let year = today.getFullYear();

  let hours = today.getHours();

  let minutes = today.getMinutes();

  if (day < 10) {
    day = "0" + day;
  }

  if (month < 10) {
    month = "0" + month;
  }

  if (hours < 10) {
    hours = "0" + hours;
  }

  if (minutes < 10) {
    minutes = "0" + minutes;
  }

  return `${year}-${month}-${day} ${hours}:${minutes}:00`;
}

autosize($("textarea.autosize"));
