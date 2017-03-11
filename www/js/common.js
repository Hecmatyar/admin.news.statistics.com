$('#startanalysis').click(function(){		
	$.ajax({ 
		type:'POST', 		
		url:'views/server/analysis.php',
		data:{
			//'test': '1'
		},
		beforeSend: function() {
			$('#analysfield').html('started');
		},
		success: function(data) {
			//console.log(JSON.parse(data));
			$('#analysfield').html(data);
			//console.log(data);
		}
	});
});

$('#test').click(function(){		
	$.ajax({ 
		type:'POST', 		
		url:'views/server/test.php',
		success: function(data) {
			$('#testfield').html(data);
			console.log(data);			
			//console.log(JSON.parse(data));
			//bypass_rssfeed(JSON.parse(data))
		}
	});
});

// function bypass_rssfeed(rss){
// 	for(var key in rss){
// 		//для каждого адреса рубать проверку
// 		console.log(key);
// 	}
// }


//добавить свойтва при наведении мышки на таблицу диапазона словаря
// $('.range-line').hover(
//   function() {
//     $(this).children('.range').addClass("l-sub");
//     $(this).children('.level').addClass("l-sub");
//   }
// );
