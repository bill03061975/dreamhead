/* Russian (UTF-8) initialisation for the jQuery UI date picker plugin. */
/* Written by Andrew Stromnov (stromnov@gmail.com). */
jQuery(function($){
	$.datepicker.regional['ru'] = {
		closeText: 'Закрыть',
		prevText: '&#x3c;Пред',
		nextText: 'След&#x3e;',
		currentText: 'Сегодня',
		monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь',
		'Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
		monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн',
		'Июл','Авг','Сен','Окт','Ноя','Дек'],
		dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
		dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
		dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
		weekHeader: 'Нед',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.regional['ua'] = {
		closeText: 'Закрити',
		prevText: '&#x3c;Попер',
		nextText: 'Наст&#x3e;',
		currentText: 'Сьогодні',
		monthNames: ['Січень','Лютий','Березень','Квітень','Травень','Червень',
		'Липень','Серпень','Вересень','Жовтень','Листопад','Грудень'],
		monthNamesShort: ['Січ','Лют','Бер','Квіт','Трав','Чер',
		'Лип','Сер','Вер','Жов','Лис','Груд'],
		dayNames: ['неділя','понеділок','вівторок','середа','четвер','п’ятниця','субота'],
		dayNamesShort: ['нед','пнд','ввт','срд','чтв','птн','сбт'],
		dayNamesMin: ['Нд','Пн','Вт','Ср','Чт','Пт','Сб'],
		weekHeader: 'Тжд',
		dateFormat: 'dd.mm.yy',
		firstDay: 1,
		isRTL: false,
		showMonthAfterYear: false,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['ru']);
});
