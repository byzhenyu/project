/**
 * 初始化地址
 * @param selector  包含三个下拉列表的选择器
 * @param province  省初始值
 * @param city      市初始值
 * @param district  区初始值
 * @param town      乡镇初始值
 * @param useName   使用名称（默认使用ID）
 */
function initAddressGD(selector, province, city, district, town, useName) {
    var $address = $(selector),
        $province = $address.find('select').eq(0),
        $city = $address.find('select').eq(1),
        $district = $address.find('select').eq(2);
        $town = $address.find('select').eq(3);

    initProvince();

    function initProvince() {
        $.post("/index.php/Core/Region/getRegionData", {key: '中国'}, function (data) {
            $province.html('<option data-code="0" value="">请选择省</option>');
            for (var i = 0; i < data.length; i++) {
                $province.append('<option data-code="' + data[i].adcode
                    + '" value="' + (useName ? data[i].name : data[i].adcode) + '">'
                    + data[i].name + '</option>');
            }
            $province.val(province);
            initCity();
        });
    }

    function initCity() {
        var province_id = $province.find('option:selected').data('code');
        var province_value = $province.find('option:selected').val();

        if (!parseInt(province_id)) {
            $city.html('<option data-code="0" value="">请选择市</option>');
            $city.val(city);
            initDistrict();
            return;
        }
        $.post("/index.php/Core/Region/getRegionData", {code: province_id, key: province_value}, function (data) {
            $city.html('<option data-code="0" value="">请选择市</option>');
            for (var i = 0; i < data.length; i++) {
                $city.append('<option data-code="' + data[i].adcode
                    + '" value="' + (useName ? data[i].name : data[i].adcode) + '">'
                    + data[i].name + '</option>');
            }
            $city.val(city);
            initDistrict();
        });
    }

    function initDistrict() {
        var city_id = $city.find('option:selected').data('code');
        var city_value = $city.find('option:selected').val();

        if (!parseInt(city_id)) {
            $district.html('<option data-code="0" value="">请选择县区</option>');
            $district.val(district);
            initTown();
            return;
        }
        $.post("/index.php/Core/Region/getRegionData", {code: city_id, key: city_value}, function (data) {
            $district.html('<option data-code="0" value="">请选择县区</option>');
            for (var i = 0; i < data.length; i++) {
                $district.append('<option data-code="' + data[i].adcode
                    + '" value="' + (useName ? data[i].name : data[i].adcode) + '">'
                    + data[i].name + '</option>');
            }
            $district.val(district);
            initTown();
        });
    }

    function initTown(){
        var district_id = $district.find('option:selected').data('code');
        var district_value = $district.find('option:selected').val();

        if(!parseInt(district_id)){
            $town.html('<option data-code="0" value="">请选择乡镇</option>');
            $town.val(town);
            return;
        }
        $.post("/index.php/Core/Region/getRegionData", {code: district_id, key: district_value}, function(data){
            $town.html('<option data-code="0" value="">请选择乡镇</option>');
            for (var i = 0; i < data.length; i++) {
                $town.append('<option data-code="' + data[i].adcode
                    + '" value="' + (useName ? data[i].name : data[i].adcode) + '">'
                    + data[i].name + '</option>');
            }
            $town.val(town);
        });
    }

    $province.change(function () {
        city = '';
        district = '';
        town = '';

        initCity();
    });

    $city.change(function () {
        district = '';
        town = '';

        initDistrict();
    });

    $district.change(function () {
        town = '';
        initTown();
    });

    $town.change(function(){
    });

}