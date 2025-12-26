// Simple i18n utility for Nhutin Website
(function(){
    'use strict';
    
    var dictionaries = {
        vi: {
            // Navigation
            nav_home: 'TRANG CHỦ',
            nav_about: 'GIỚI THIỆU',
            nav_products: 'SẢN PHẨM',
            nav_applications: 'ỨNG DỤNG',
            nav_news: 'TIN TỨC',
            nav_contact: 'LIÊN HỆ',
            
            // Page Header
            page_about_title: 'GIỚI THIỆU',
            page_products_title: 'SẢN PHẨM',
            page_contact_title: 'LIÊN HỆ',
            breadcrumb_home: 'Trang Chủ',
            breadcrumb_about: 'Giới Thiệu',
            breadcrumb_products: 'Sản Phẩm',
            breadcrumb_contact: 'Liên Hệ',
            
            // Company Introduction
            intro_subtitle: 'Giới Thiệu',
            intro_title: 'CÔNG TY CỔ PHẦN NHƯ TÍN',
            intro_text_1: 'Công ty cổ phần Như Tín được thành lập vào tháng 01 năm 2010. Chúng tôi luôn nỗ lực tạo dựng mối quan hệ hợp tác tốt đẹp với các đối tác và không ngừng phát triển hoạt động kinh doanh trên cơ sở mang lại lợi ích song phương.',
            intro_text_2: 'Chúng tôi chuyên cung cấp chất đốt sinh khối bã điều (bã vỏ hạt điều sau ép dầu).',
            
            // Biomass Solutions
            biomass_title: 'Giải Pháp Chất Đốt Sinh Khối',
            biomass_intro: 'Là đơn vị tiên phong khởi xướng việc ứng dụng chất đốt bã điều cho các lò hơi công nghiệp với hàng chục năm kinh nghiệm. Chúng tôi tự tin mang đến cho khách hàng các giải pháp để sử dụng hiệu quả chất đốt bã điều (hay các chất đốt sinh khối khác) bao gồm:',
            
            biomass_solution_1_title: 'Thiết Bị Cải Tiến Lò Hơi',
            biomass_solution_1_desc: 'Cung cấp các thiết bị cải tiến lò hơi để đốt bã điều hiệu quả, đảm bảo hoạt động ổn định và bền bỉ',
            
            biomass_solution_2_title: 'Tiết Kiệm Chi Phí Đến 50%',
            biomass_solution_2_desc: 'Tư vấn cách sử dụng chất đốt bã điều hiệu quả nhằm tiết kiệm chi phí sản xuất lên tới 50% so với khi chỉ đốt than',
            
            biomass_solution_3_title: 'Bảo Vệ Tuổi Thọ Thiết Bị',
            biomass_solution_3_desc: 'Tư vấn cách vận hành không ảnh hưởng đến tuổi thọ thiết bị, giúp doanh nghiệp tối ưu hóa đầu tư',
            
            biomass_solution_4_title: 'Xử Lý Khói Thải Thân Thiện',
            biomass_solution_4_desc: 'Tư vấn cách xử lý khói thải không gây ảnh hưởng đến môi trường xung quanh, đáp ứng tiêu chuẩn môi trường',
            
            // Keith Walking Floor
            keith_subtitle: 'Công Nghệ Tiên Tiến Từ Mỹ',
            keith_title: 'Hệ Thống Xuống Hàng Tự Động KEITH® Walking Floor',
            keith_text_1: 'Bên cạnh đó, từ năm 2018, Như Tín là đơn vị tiên phong tại Việt Nam đầu tư và đưa vào sử dụng công nghệ xuống hàng tự động (tự đổ) cho xe tải thùng, xe đầu kéo container từ Mỹ.',
            keith_text_2: 'Nhận thấy được những hiệu quả to lớn từ việc đầu tư này, Như Tín mong muốn được giới thiệu công nghệ này đến các doanh nghiệp trong nước, đóng góp vào lợi ích thiết thực cho Quý Khách Hàng.',
            keith_text_3: 'Trải qua quá trình dài tìm hiểu, sàng lọc và ứng dụng thực tế, Như Tín hiện là Đại lý ủy quyền của KEITH® Mfg. Co, tại Việt Nam để phân phối và lắp đặt hệ thống xếp dỡ hàng hóa tự động cho xe tải, container chất lượng, hiệu quả và giá thành hợp lý cho thị trường Việt Nam.',
            keith_text_4: 'Với ưu điểm tinh gọn, linh hoạt, phù hợp với đa dạng hàng hóa, đa dạng tải trọng (từ 5 tấn đến 80 tấn), độ bền cao, gần như không cần bảo trì, vận hành đơn giản, lắp đặt nhanh chóng, hệ thống dễ dàng phù hợp với các loại xe tải thùng/container sẵn có của Quý Khách Hàng.',
            
            // Features
            features_subtitle: 'Ưu Điểm Vượt Trội',
            features_title: 'Tại Sao Chọn Như Tín?',
            
            feature_1_title: 'Tự Động Hóa Xếp Dỡ',
            feature_1_desc: 'Tăng tốc logistics với đội xe chuyên dụng nhiều xe, đa tải trọng, trang bị hệ thống xuống hàng tự động từ Mỹ.',
            
            feature_2_title: 'Tối Ưu Thời Gian',
            feature_2_desc: 'Thời gian dỡ hàng chỉ mất 5 phút. Giao hàng nhanh tận kho quý khách hàng trong ngày theo yêu cầu!',
            
            feature_3_title: 'Nhiên Liệu Sạch',
            feature_3_desc: 'Dẫn đầu thị trường sinh khối bã điều tại Miền Nam với hàng chục năm kinh nghiệm. Năng lượng xanh cho tương lai.',
            
            feature_4_title: 'Linh Hoạt & Đa Dạng',
            feature_4_desc: 'Tinh gọn, linh hoạt, phù hợp với đa dạng hàng hóa, đa dạng tải trọng (từ 5 tấn đến 80 tấn), độ bền cao, gần như không cần bảo trì.',
            
            feature_5_title: 'Uy Tín - Tận Tâm',
            feature_5_desc: 'Đội ngũ quản lý uy tín, tinh thần trách nhiệm cao, linh động, tinh gọn & hiệu quả. Cam kết mang đến sản phẩm chất lượng với giá cả cạnh tranh nhất.',
            
            feature_6_title: 'Hỗ Trợ 24/24',
            feature_6_desc: 'Hỗ trợ miễn phí 24/24. Liên hệ: 0907 917 301',
            
            // Commitment
            commitment_subtitle: 'Đội Ngũ & Cam Kết',
            commitment_title: 'Cam Kết Chất Lượng Từ Như Tín',
            
            commitment_team_title: 'Đội Ngũ Chuyên Nghiệp',
            commitment_team_desc: 'Với đội ngũ quản lý uy tín, tinh thần trách nhiệm cao, linh động, tinh gọn & hiệu quả, chúng tôi cam kết mang đến cho Quý khách hàng những sản phẩm chất lượng với giá cả cạnh tranh nhất, tiến độ giao hàng đảm bảo, đáp ứng yêu cầu đa dạng của Quý khách.',
            
            commitment_motto_title: 'Phương Châm Hoạt Động',
            commitment_motto_desc: 'Với phương châm uy tín và hợp tác bền vững, Như Tín luôn nỗ lực cả về nhân lực và vật lực để xây dựng uy tín thương hiệu, niềm tin và sự hài lòng của khách hàng đối với sản phẩm và dịch vụ mà chúng tôi cung cấp.',
            
            // Final Message
            final_title: 'Lời cảm ơn & cam kết',
            final_text_1: 'Sự tin tưởng và ủng hộ của khách hàng trong suốt thời gian qua là nguồn động viên to lớn trên con đường phát triển của Như Tín. Chúng tôi sẽ không ngừng hoàn thiện, phục vụ khách hàng tốt nhất để xứng đáng với niềm tin đó.',
            final_text_2: 'Công ty cổ phần Như Tín sẵn sàng đáp ứng các yêu cầu của Quý khách hàng với những sản phẩm & dịch vụ chất lượng và giá cả cạnh tranh nhất!',
            final_cta: 'Liên Hệ Ngay',
            
            // Products Page
            products_subtitle: 'Sản Phẩm Chính',
            products_title: 'Giải Pháp Toàn Diện Từ Như Tín',
            products_intro: 'Như Tín cung cấp hai sản phẩm chính: Nhiên liệu sinh khối bã điều và Hệ thống sàn trượt tự đổ KEITH® Walking Floor - giải pháp hàng đầu cho nhu cầu năng lượng và vận tải của bạn.',
            
            product_biomass_title: 'Nhiên Liệu Sinh Khối Bã Điều',
            product_biomass_subtitle: 'Cashew Shell Biomass Fuel',
            product_biomass_desc: 'Như Tín là đơn vị tiên phong tại Việt Nam trong việc ứng dụng chất đốt sinh khối bã điều cho các lò hơi công nghiệp với hàng chục năm kinh nghiệm.',
            product_biomass_advantage_title: 'Ưu điểm nổi bật:',
            product_biomass_advantage_1: 'Tiết kiệm chi phí sản xuất lên tới 50% so với đốt than',
            product_biomass_advantage_2: 'Nhiên liệu sạch, thân thiện với môi trường',
            product_biomass_advantage_3: 'Đội xe chuyên dụng giao hàng nhanh tận kho',
            product_biomass_advantage_4: 'Tư vấn cách vận hành không ảnh hưởng tuổi thọ thiết bị',
            product_biomass_advantage_5: 'Xử lý khói thải đạt tiêu chuẩn môi trường',
            product_biomass_cta: 'Tìm Hiểu Thêm',
            
            product_walking_floor_title: 'Sàn Trượt Tự Đổ',
            product_walking_floor_subtitle: 'KEITH® Walking Floor System',
            product_walking_floor_desc: 'Từ năm 2018, Như Tín là đơn vị tiên phong tại Việt Nam và là Đại lý ủy quyền của KEITH® Mfg. Co, tại Việt Nam để phân phối và lắp đặt hệ thống xếp dỡ hàng hóa tự động.',
            product_walking_floor_advantage_title: 'Ưu điểm nổi bật:',
            product_walking_floor_advantage_1: 'Xuống hàng chỉ mất 5 phút, hiệu quả gấp 4 lần',
            product_walking_floor_advantage_2: 'Phù hợp tải trọng từ 5 tấn đến 80 tấn',
            product_walking_floor_advantage_3: 'Độ bền cao, gần như không cần bảo trì',
            product_walking_floor_advantage_4: 'Lắp đặt nhanh chóng, phù hợp với xe sẵn có',
            product_walking_floor_advantage_5: 'Công nghệ hàng đầu thế giới từ Mỹ',
            product_walking_floor_cta: 'Tìm Hiểu Thêm',
            
            products_why_subtitle: 'Tại Sao Chọn Như Tín?',
            products_why_title: 'Cam Kết Chất Lượng & Dịch Vụ',
            products_why_1_title: 'Tiên Phong',
            products_why_1_desc: 'Đơn vị đầu tiên tại Việt Nam ứng dụng công nghệ hiện đại từ Mỹ',
            products_why_2_title: 'Kinh Nghiệm',
            products_why_2_desc: 'Hàng chục năm kinh nghiệm trong lĩnh vực năng lượng và vận tải',
            products_why_3_title: 'Uy Tín',
            products_why_3_desc: 'Đại lý ủy quyền của KEITH® Mfg. Co, tại Việt Nam',
            products_why_4_title: 'Hỗ Trợ 24/7',
            products_why_4_desc: 'Đội ngũ tư vấn chuyên nghiệp, hỗ trợ tận tâm',
            
            products_cta_title: 'Bạn Cần Tư Vấn?',
            products_cta_text: 'Liên hệ ngay với chúng tôi để nhận tư vấn miễn phí về sản phẩm và giải pháp phù hợp nhất cho doanh nghiệp của bạn.',
            products_cta_button: 'Liên Hệ Ngay',
            
            // Contact Page
            contact_form_subtitle: 'Gửi Tin Nhắn',
            contact_form_title: 'Liên Hệ Với Chúng Tôi',
            contact_form_desc: 'Vui lòng điền thông tin vào form bên dưới, chúng tôi sẽ liên hệ lại với bạn trong thời gian sớm nhất.',
            contact_form_name: 'Họ và tên *',
            contact_form_email: 'Email *',
            contact_form_phone: 'Số điện thoại',
            contact_form_service: 'Dịch vụ quan tâm',
            contact_form_service_select: 'Chọn dịch vụ',
            contact_form_service_biomass: 'Bã điều',
            contact_form_service_walking_floor: 'Sàn trượt tự đổ',
            contact_form_subject: 'Tiêu đề',
            contact_form_message: 'Nội dung tin nhắn *',
            contact_form_submit: 'Gửi Tin Nhắn',
            
            contact_info_title: 'Thông Tin Liên Hệ',
            contact_info_address_label: 'Địa chỉ',
            contact_info_phone_label: 'Điện thoại',
            contact_info_email_label: 'Email',
            contact_info_zalo_label: 'Zalo'
        },
        en: {
            // Navigation
            nav_home: 'HOME',
            nav_about: 'ABOUT US',
            nav_products: 'PRODUCTS',
            nav_applications: 'APPLICATIONS',
            nav_news: 'NEWS',
            nav_contact: 'CONTACT',
            
            // Page Header
            page_about_title: 'ABOUT US',
            page_products_title: 'PRODUCTS',
            page_contact_title: 'CONTACT',
            breadcrumb_home: 'Home',
            breadcrumb_about: 'About Us',
            breadcrumb_products: 'Products',
            breadcrumb_contact: 'Contact',
            
            // Company Introduction
            intro_subtitle: 'About Us',
            intro_title: 'NHU TIN CORPORATION',
            intro_text_1: 'Nhu Tin Corporation was established in January 2010. We always strive to build good cooperative relationships with partners and continuously develop business activities on the basis of mutual benefit.',
            intro_text_2: 'We specialize in supplying cashew nut shell biomass fuel (cashew nut shell cake after oil pressing).',
            
            // Biomass Solutions
            biomass_title: 'Biomass Fuel Solutions',
            biomass_intro: 'As a pioneering unit in initiating the application of cashew nut shell fuel for industrial boilers with decades of experience. We are confident in providing customers with solutions to effectively use cashew nut shell fuel (or other biomass fuels) including:',
            
            biomass_solution_1_title: 'Boiler Improvement Equipment',
            biomass_solution_1_desc: 'Providing improved boiler equipment for efficient cashew nut shell burning, ensuring stable and durable operation',
            
            biomass_solution_2_title: 'Save Up To 50% Costs',
            biomass_solution_2_desc: 'Consulting on how to use cashew nut shell fuel effectively to save production costs up to 50% compared to coal only',
            
            biomass_solution_3_title: 'Equipment Life Protection',
            biomass_solution_3_desc: 'Consulting on operation methods that do not affect equipment lifespan, helping businesses optimize investment',
            
            biomass_solution_4_title: 'Eco-Friendly Emission Treatment',
            biomass_solution_4_desc: 'Consulting on emission treatment methods that do not affect the surrounding environment, meeting environmental standards',
            
            // Keith Walking Floor
            keith_subtitle: 'Advanced Technology From USA',
            keith_title: 'KEITH® Walking Floor Automatic Unloading System',
            keith_text_1: 'In addition, since 2018, Nhu Tin has been the pioneering unit in Vietnam to invest and put into use automatic unloading (self-tipping) technology for trucks and container trailers from the USA.',
            keith_text_2: 'Recognizing the great benefits from this investment, Nhu Tin wishes to introduce this technology to domestic enterprises, contributing practical benefits to our customers.',
            keith_text_3: 'After a long process of research, filtering and practical application, Nhu Tin is currently the Authorized dealer of KEITH® Mfg. Co, in Vietnam to distribute and install automatic cargo handling systems for trucks and containers with quality, efficiency and reasonable prices for the Vietnamese market.',
            keith_text_4: 'With the advantages of compactness, flexibility, suitable for diverse goods, diverse load capacity (from 5 tons to 80 tons), high durability, almost no maintenance required, simple operation, quick installation, the system easily fits the types of trucks/containers available to customers.',
            
            // Features
            features_subtitle: 'Outstanding Advantages',
            features_title: 'Why Choose Nhu Tin?',
            
            feature_1_title: 'Automated Loading & Unloading',
            feature_1_desc: 'Speed up logistics with a specialized fleet of multiple vehicles, diverse loads, equipped with automatic unloading systems from the USA.',
            
            feature_2_title: 'Time Optimization',
            feature_2_desc: 'Unloading time only takes 5 minutes. Fast delivery to customer warehouse on the same day upon request!',
            
            feature_3_title: 'Clean Fuel',
            feature_3_desc: 'Leading the cashew nut shell biomass market in Southern Vietnam with decades of experience. Green energy for the future.',
            
            feature_4_title: 'Flexible & Diverse',
            feature_4_desc: 'Compact, flexible, suitable for diverse goods, diverse load capacity (from 5 tons to 80 tons), high durability, almost no maintenance required.',
            
            feature_5_title: 'Reputation - Dedication',
            feature_5_desc: 'Reputable management team, high sense of responsibility, flexible, compact & efficient. Committed to bringing customers quality products at the most competitive prices.',
            
            feature_6_title: '24/7 Support',
            feature_6_desc: 'Free 24/7 support. Contact: 0907 917 301',
            
            // Commitment
            commitment_subtitle: 'Team & Commitment',
            commitment_title: 'Quality Commitment From Nhu Tin',
            
            commitment_team_title: 'Professional Team',
            commitment_team_desc: 'With a reputable management team, high sense of responsibility, flexible, compact & efficient, we are committed to bringing customers quality products at the most competitive prices, guaranteed delivery time, meeting diverse customer requirements.',
            
            commitment_motto_title: 'Operating Motto',
            commitment_motto_desc: 'With the motto of reputation and sustainable cooperation, Nhu Tin always strives in both human and material resources to build brand reputation, trust and customer satisfaction with the products and services we provide.',
            
            // Final Message
            final_title: 'Thanks & Commitment',
            final_text_1: 'Customer trust and support over the years has been a great motivation on Nhu Tin\'s development path. We will constantly improve and serve customers best to be worthy of that trust.',
            final_text_2: 'Nhu Tin Corporation is ready to meet customer requirements with quality products & services at the most competitive prices!',
            final_cta: 'Contact Now',
            
            // Products Page
            products_subtitle: 'Main Products',
            products_title: 'Comprehensive Solutions From Nhu Tin',
            products_intro: 'Nhu Tin provides two main products: Cashew shell biomass fuel and KEITH® Walking Floor automatic unloading system - leading solutions for your energy and transportation needs.',
            
            product_biomass_title: 'Cashew Shell Biomass Fuel',
            product_biomass_subtitle: 'Cashew Shell Biomass Fuel',
            product_biomass_desc: 'Nhu Tin is a pioneering unit in Vietnam in applying cashew shell biomass fuel for industrial boilers with decades of experience.',
            product_biomass_advantage_title: 'Outstanding Advantages:',
            product_biomass_advantage_1: 'Save production costs up to 50% compared to coal',
            product_biomass_advantage_2: 'Clean fuel, environmentally friendly',
            product_biomass_advantage_3: 'Specialized fleet for fast delivery to warehouse',
            product_biomass_advantage_4: 'Consulting on operation methods that do not affect equipment lifespan',
            product_biomass_advantage_5: 'Emission treatment meets environmental standards',
            product_biomass_cta: 'Learn More',
            
            product_walking_floor_title: 'Walking Floor System',
            product_walking_floor_subtitle: 'KEITH® Walking Floor System',
            product_walking_floor_desc: 'Since 2018, Nhu Tin has been a pioneering unit in Vietnam and is the Authorized dealer of KEITH® Mfg. Co, in Vietnam to distribute and install automatic cargo handling systems.',
            product_walking_floor_advantage_title: 'Outstanding Advantages:',
            product_walking_floor_advantage_1: 'Unloading takes only 5 minutes, 4 times more efficient',
            product_walking_floor_advantage_2: 'Suitable for load capacity from 5 tons to 80 tons',
            product_walking_floor_advantage_3: 'High durability, almost no maintenance required',
            product_walking_floor_advantage_4: 'Quick installation, suitable for existing vehicles',
            product_walking_floor_advantage_5: 'World-class technology from USA',
            product_walking_floor_cta: 'Learn More',
            
            products_why_subtitle: 'Why Choose Nhu Tin?',
            products_why_title: 'Quality & Service Commitment',
            products_why_1_title: 'Pioneering',
            products_why_1_desc: 'First unit in Vietnam to apply modern technology from USA',
            products_why_2_title: 'Experience',
            products_why_2_desc: 'Decades of experience in energy and transportation fields',
            products_why_3_title: 'Reputation',
            products_why_3_desc: 'Authorized dealer of KEITH® Mfg. Co, in Vietnam',
            products_why_4_title: '24/7 Support',
            products_why_4_desc: 'Professional consulting team, dedicated support',
            
            products_cta_title: 'Need Consultation?',
            products_cta_text: 'Contact us now to receive free consultation on products and solutions most suitable for your business.',
            products_cta_button: 'Contact Now',
            
            // Contact Page
            contact_form_subtitle: 'Send Message',
            contact_form_title: 'Contact Us',
            contact_form_desc: 'Please fill in the information below, we will contact you as soon as possible.',
            contact_form_name: 'Your Name *',
            contact_form_email: 'Your Email *',
            contact_form_phone: 'Phone Number',
            contact_form_service: 'Service of Interest',
            contact_form_service_select: 'Select A Service',
            contact_form_service_biomass: 'Cashew Shell Biomass',
            contact_form_service_walking_floor: 'Walking Floor System',
            contact_form_subject: 'Subject',
            contact_form_message: 'Message *',
            contact_form_submit: 'Send Message',
            
            contact_info_title: 'Contact Information',
            contact_info_address_label: 'Address',
            contact_info_phone_label: 'Phone',
            contact_info_email_label: 'Email',
            contact_info_zalo_label: 'Zalo'
        }
    };
    
    var currentLang = 'vi';
    
    // Initialize language from localStorage or default to Vietnamese
    function init() {
        var savedLang = localStorage.getItem('language');
        if (savedLang && dictionaries[savedLang]) {
            currentLang = savedLang;
        }
        translatePage();
        updateLangButton();
    }
    
    // Translate all elements with data-i18n attribute
    function translatePage() {
        var elements = document.querySelectorAll('[data-i18n]');
        elements.forEach(function(el) {
            var key = el.getAttribute('data-i18n');
            var translation = dictionaries[currentLang][key];
            if (translation) {
                el.textContent = translation;
            }
        });
        
        // Translate placeholders
        var placeholderElements = document.querySelectorAll('[data-i18n-placeholder]');
        placeholderElements.forEach(function(el) {
            var key = el.getAttribute('data-i18n-placeholder');
            var translation = dictionaries[currentLang][key];
            if (translation) {
                el.placeholder = translation;
            }
        });
        
        // Translate option elements
        var optionElements = document.querySelectorAll('option[data-i18n]');
        optionElements.forEach(function(el) {
            var key = el.getAttribute('data-i18n');
            var translation = dictionaries[currentLang][key];
            if (translation) {
                el.textContent = translation;
            }
        });
    }
    
    // Switch language
    function switchLanguage(lang) {
        if (dictionaries[lang]) {
            currentLang = lang;
            localStorage.setItem('language', lang);
            translatePage();
            updateLangButton();
        }
    }
    
    // Update language toggle button text
    function updateLangButton() {
        var langBtn = document.getElementById('langToggle');
        if (langBtn) {
            langBtn.textContent = currentLang === 'vi' ? 'EN' : 'VI';
        }
    }
    
    // Expose public API
    window.i18n = {
        init: init,
        switchLanguage: switchLanguage,
        getCurrentLang: function() { return currentLang; }
    };
    
    // Auto-initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();

