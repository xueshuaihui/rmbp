(function($) {
    $.fn.extend({
        "date_select": function(options) {
            var date = new Date();

            var opt = $.extend({
                year_from: 2000,
                year_to: (date.getFullYear()) - 0,
                set_date: date.getFullYear() + '/' + (date.getMonth() + 1) + '/' + date.getDate(),
                default_date: "Off"
            }, options);

            var NUM_MONTH = 12;
            var ARR_DAY = [31,28,31,30,31,30,31,31,30,31,30,31];


            return this.each(function(){

                var s_select = $(this).find("select");
                // 找到三个select元素
                var s_year  = s_select.eq(0);
                var s_month = s_select.eq(1);
                var s_day   = s_select.eq(2);
                // 保存已选
                var default_day;

                function _init() {
                    // 插入年份
                    _insertData(s_year, opt.year_from, (opt.year_to - opt.year_from + 1));
                    // 插入月份
                    _insertData(s_month, 1, NUM_MONTH);
                    // 绑定事件
                    s_year.bind("change", _operDay);
                    s_month.bind("change", _operDay);
                    s_day.bind("change", changeEvent_day);
                    // 插入日
                    _operDay();
                    // 设置默认日期
                    _setDefault();
                }

                _init();

                // 设置默认日期
                function _setDefault() {
                    if (opt.default_date == "On") {
                        var d       = opt.set_date.split('/');
                        var d_year  = d[0];
                        var d_month = d[1];
                        var d_day   = d[2];

                        s_year.val(d_year);
                        s_month.val(d_month);
                        _operDay();
                        s_day.val(d_day);
                        // 设置默认
                        default_day = d_day;
                    }
                }

                function changeEvent_day() {
                    default_day = $(this).attr("value");
                }

                // 处理日
                function _operDay() {
                    var year = s_year.val();
                    var month = s_month.val();
                    var n;

                    n = _getDayByYearMonth(year, month);

                    if (s_day.find("option").length == n) return;
                    _insertData(s_day, 1, n);
                    // 设置日为已选，如果超出则设为当月最后一天
                    s_day.attr("value", default_day > n ? default_day = n : default_day );
                }

                // 通过年月得到当月天数
                function _getDayByYearMonth(year, month) {
                    if (month == 2 && year % 4 == 0) {
                        return 29;
                    } else {
                        return ARR_DAY[month - 1];
                    }
                }

                // 对select插入数据
                function _insertData(elem, from, n) {
                    var s = [];

                    elem.html("");
                    s.push('<option value="-1">请选择</option>');
                    for (var i=0; i<n; i++) {
                        s.push("<option value="+ from +">"+ from +"</option>");
                        from ++;
                    }

                    elem.html(s.join(""));
                }

            })
        }
    })
})(jQuery);