/**
 * 初始化地址
 * @param selector  包含三个下拉列表的选择器
 * @param province  省初始值
 * @param city      市初始值
 * @param district  区初始值
 * @param useName   使用名称（默认使用ID）
 */
function initAddress(selector, province, city, district, useName) {
    var $address = $(selector),
        $province = $address.find('select').eq(0),
        $city = $address.find('select').eq(1),
        $district = $address.find('select').eq(2);

    initProvince();

    function initProvince() {
        $.post("/index.php/Core/Region/getDataByParentId", {}, function (data) {
            $province.html('<option data-id="0" value="">请选择省</option>');
            for (var i = 0; i < data.length; i++) {
                $province.append('<option data-id="' + data[i].id
                    + '" value="' + (useName ? data[i].region_name : data[i].id) + '">'
                    + data[i].region_name + '</option>');
            }
            $province.val(province);
            initCity();
        });
    }

    function initCity() {
        var province_id = $province.find('option:selected').data('id');

        if (!parseInt(province_id)) {
            $city.html('<option data-id="0" value="">请选择市</option>');
            $city.val(city);
            initDistrict();
            return;
        }
        $.post("/index.php/Core/Region/getDataByParentId", {parent_id: province_id}, function (data) {
            $city.html('<option data-id="0" value="">请选择市</option>');
            for (var i = 0; i < data.length; i++) {
                $city.append('<option data-id="' + data[i].id
                    + '" value="' + (useName ? data[i].region_name : data[i].id) + '">'
                    + data[i].region_name + '</option>');
            }
            $city.val(city);
            initDistrict();
        });
    }

    function initDistrict() {
        var city_id = $city.find('option:selected').data('id');

        if (!parseInt(city_id)) {
            $district.html('<option data-id="0" value="">请选择县区</option>');
            $district.val(district);
            return;
        }
        $.post("/index.php/Core/Region/getDataByParentId", {parent_id: city_id}, function (data) {
            $district.html('<option data-id="0" value="">请选择县区</option>');
            for (var i = 0; i < data.length; i++) {
                $district.append('<option data-id="' + data[i].id
                    + '" value="' + (useName ? data[i].region_name : data[i].id) + '">'
                    + data[i].region_name + '</option>');
            }
            $district.val(district);
        });
    }

    $province.change(function () {
        city = '';
        district = '';

        initCity();
    });

    $city.change(function () {
        district = '';

        initDistrict();
    });

    $district.change(function () {
    });

}



