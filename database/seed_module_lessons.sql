-- ============================================================
--  Arandia College eLMS — Starter lessons (2 modules per subject)
--  File: database/seed_module_lessons.sql
--
--  Import AFTER migration_modules.sql, seed_hs_subjects.sql and
--  seed_shs_subjects.sql. Safe to run more than once: a slot that
--  already has a module is left alone.
--  67 subjects x 2 modules = 134 lessons.
-- ============================================================
SET NAMES utf8mb4;

-- HS7-FIL
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Panitikan ng Mindanao: Kuwentong-bayan', 'Ang kuwentong-bayan ay salaysay na nagpasalin-salin sa bibig ng mga tao at nagpapakita ng kultura at paniniwala ng isang pook.', '<p>Ang kuwentong-bayan ay salaysay na nagpasalin-salin sa bibig ng mga tao at nagpapakita ng kultura at paniniwala ng isang pook.</p><h3>Mahahalagang Punto</h3><ul><li>Karaniwang tauhan ang karaniwang tao, hayop, o nilalang na may kapangyarihan.</li><li>Ipinapakita nito ang kaugalian, paniniwala, at pagpapahalaga ng isang pamayanan.</li><li>Halimbawa sa Mindanao ang mga kuwento ng mga Maranao at Manobo.</li></ul><h3>Subukan Mo</h3><p>Magbigay ng isang kuwentong-bayan na alam mo at isulat ang aral na makukuha rito.</p>', 1 FROM courses c
WHERE c.course_code = 'HS7-FIL' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Pabula at Alamat', 'Ang pabula ay kuwentong hayop ang tauhan at may aral, samantalang ang alamat ay nagsasalaysay ng pinagmulan ng isang bagay o pook.', '<p>Ang pabula ay kuwentong hayop ang tauhan at may aral, samantalang ang alamat ay nagsasalaysay ng pinagmulan ng isang bagay o pook.</p><h3>Mahahalagang Punto</h3><ul><li>Sa pabula, kumikilos at nagsasalita ang mga hayop na parang tao.</li><li>Ang alamat ay sumasagot sa tanong na &quot;Paano nagsimula ito?&quot;</li><li>Pareho silang nagtuturo ng mabuting asal sa mambabasa.</li></ul><h3>Subukan Mo</h3><p>Sumulat ng maikling alamat tungkol sa isang bagay sa inyong tahanan.</p>', 1 FROM courses c
WHERE c.course_code = 'HS7-FIL' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS7-ENGLISH
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Philippine Folk Literature', 'Folk literature is the oral tradition of our ancestors: myths, legends, and folk tales passed down through generations.', '<p>Folk literature is the oral tradition of our ancestors: myths, legends, and folk tales passed down through generations.</p><h3>Key points</h3><ul><li>Myths explain the origin of the world and natural events.</li><li>Legends tell how a place, thing, or name came to be.</li><li>Folk tales often teach values such as honesty and hard work.</li></ul><h3>Try this</h3><p>Retell a Philippine legend in your own words in one paragraph.</p>', 1 FROM courses c
WHERE c.course_code = 'HS7-ENGLISH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Parts of Speech', 'Every word in a sentence has a job, and knowing these jobs helps you write clearly.', '<p>Every word in a sentence has a job, and knowing these jobs helps you write clearly.</p><h3>Key points</h3><ul><li>Nouns name people, places, things, and ideas; pronouns replace nouns.</li><li>Verbs show action or state of being.</li><li>Adjectives describe nouns, while adverbs describe verbs, adjectives, or other adverbs.</li></ul><h3>Try this</h3><p>Write a sentence that uses a noun, a verb, an adjective, and an adverb, then label each.</p>', 1 FROM courses c
WHERE c.course_code = 'HS7-ENGLISH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS7-MATH
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Sets', 'A set is a well-defined collection of objects called elements.', '<p>A set is a well-defined collection of objects called elements.</p><h3>Key points</h3><ul><li>Sets are written with braces, like A = {1, 2, 3}.</li><li>The union of A and B contains elements in A or B; the intersection contains elements in both.</li><li>Venn diagrams help show how sets relate to each other.</li></ul><h3>Try this</h3><p>If A = {2, 4, 6, 8} and B = {4, 8, 12}, find A ∪ B and A ∩ B.</p>', 1 FROM courses c
WHERE c.course_code = 'HS7-MATH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Integers and Their Operations', 'Integers are whole numbers and their negatives, including zero.', '<p>Integers are whole numbers and their negatives, including zero.</p><h3>Key points</h3><ul><li>Adding a negative number is the same as subtracting its positive value.</li><li>Same signs: add and keep the sign. Different signs: subtract and use the sign of the larger absolute value.</li><li>In multiplication and division, same signs give a positive answer and different signs give a negative answer.</li></ul><h3>Try this</h3><p>Solve: (−8) + 5, (−6) × (−3), and 20 ÷ (−4).</p>', 1 FROM courses c
WHERE c.course_code = 'HS7-MATH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS7-SCI
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'The Scientific Method', 'The scientific method is a step-by-step way of answering questions about the world.', '<p>The scientific method is a step-by-step way of answering questions about the world.</p><h3>Key points</h3><ul><li>Steps: observe, ask a question, form a hypothesis, experiment, analyze, and conclude.</li><li>A good experiment changes only one variable at a time.</li><li>Results must be recorded honestly, even if they do not support the hypothesis.</li></ul><h3>Try this</h3><p>Design a simple experiment to test whether sunlight affects plant growth.</p>', 1 FROM courses c
WHERE c.course_code = 'HS7-SCI' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Solutions', 'A solution is a uniform mixture of a solute dissolved in a solvent.', '<p>A solution is a uniform mixture of a solute dissolved in a solvent.</p><h3>Key points</h3><ul><li>In salt water, salt is the solute and water is the solvent.</li><li>Water is called the universal solvent.</li><li>Temperature and stirring affect how fast a solute dissolves.</li></ul><h3>Try this</h3><p>List three solutions found at home and identify the solute and solvent of each.</p>', 1 FROM courses c
WHERE c.course_code = 'HS7-SCI' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS7-AP
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Heograpiya ng Asya', 'Ang Asya ang pinakamalaking kontinente sa daigdig at nahahati sa limang rehiyon.', '<p>Ang Asya ang pinakamalaking kontinente sa daigdig at nahahati sa limang rehiyon.</p><h3>Mahahalagang Punto</h3><ul><li>Ang mga rehiyon ay Hilaga, Kanluran, Timog, Silangan, at Timog-Silangang Asya.</li><li>Matatagpuan dito ang Mt. Everest, ang pinakamataas na bundok sa mundo.</li><li>Ang Pilipinas ay bahagi ng Timog-Silangang Asya.</li></ul><h3>Subukan Mo</h3><p>Gumuhit ng mapa ng Asya at kulayan ang limang rehiyon.</p>', 1 FROM courses c
WHERE c.course_code = 'HS7-AP' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Likas na Yaman ng Asya', 'Mayaman ang Asya sa likas na yaman na mahalaga sa kabuhayan ng mga tao.', '<p>Mayaman ang Asya sa likas na yaman na mahalaga sa kabuhayan ng mga tao.</p><h3>Mahahalagang Punto</h3><ul><li>Mayroon itong mga yamang-lupa, yamang-tubig, yamang-mineral, at yamang-gubat.</li><li>Kanlurang Asya ang may pinakamalaking reserba ng langis.</li><li>Kailangan ang wastong paggamit upang hindi maubos ang likas na yaman.</li></ul><h3>Subukan Mo</h3><p>Magtala ng tatlong likas na yaman ng Pilipinas at kung paano ito mapangangalagaan.</p>', 1 FROM courses c
WHERE c.course_code = 'HS7-AP' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS7-ESP
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Pagkilala sa Sarili', 'Ang pagkilala sa sarili ang unang hakbang sa paglago bilang tao.', '<p>Ang pagkilala sa sarili ang unang hakbang sa paglago bilang tao.</p><h3>Mahahalagang Punto</h3><ul><li>Kilalanin ang iyong kalakasan at kahinaan.</li><li>Ang pagdadalaga at pagbibinata ay panahon ng maraming pagbabago sa katawan at damdamin.</li><li>Normal ang mga pagbabagong ito at bahagi ng paglaki.</li></ul><h3>Subukan Mo</h3><p>Gumawa ng listahan ng tatlong kalakasan at isang kahinaan na nais mong paunlarin.</p>', 1 FROM courses c
WHERE c.course_code = 'HS7-ESP' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Talento at Kakayahan', 'Ang bawat tao ay may natatanging talento na maaaring paunlarin.', '<p>Ang bawat tao ay may natatanging talento na maaaring paunlarin.</p><h3>Mahahalagang Punto</h3><ul><li>Ang talento ay likas na galing; ang kakayahan ay nalilinang sa pagsasanay.</li><li>Mahalaga ang tiwala sa sarili upang mapaunlad ang talento.</li><li>Gamitin ang talento upang makatulong sa iba.</li></ul><h3>Subukan Mo</h3><p>Paano mo magagamit ang iyong talento upang makatulong sa iyong pamilya o paaralan?</p>', 1 FROM courses c
WHERE c.course_code = 'HS7-ESP' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS7-MAPEH
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Music: Music of the Philippine Lowlands', 'Lowland music in the Philippines blends native, Spanish, and folk influences.', '<p>Lowland music in the Philippines blends native, Spanish, and folk influences.</p><h3>Key points</h3><ul><li>Folk songs like &quot;Bahay Kubo&quot; and &quot;Leron Leron Sinta&quot; describe daily life.</li><li>The rondalla is an ensemble of plucked string instruments like the bandurria.</li><li>Religious music such as the pasyon is sung during Holy Week.</li></ul><h3>Try this</h3><p>Sing a Filipino folk song and identify its meter and mood.</p>', 1 FROM courses c
WHERE c.course_code = 'HS7-MAPEH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Arts: Arts and Crafts of the Philippine Lowlands', 'Lowland Filipinos created arts and crafts from local materials that reflect their way of life.', '<p>Lowland Filipinos created arts and crafts from local materials that reflect their way of life.</p><h3>Key points</h3><ul><li>Examples include weaving, pottery, and wood carving.</li><li>Paete, Laguna is known for wood carving; Vigan for burnay jars.</li><li>Many designs use patterns inspired by nature.</li></ul><h3>Try this</h3><p>Create a simple design inspired by a Philippine lowland craft.</p>', 1 FROM courses c
WHERE c.course_code = 'HS7-MAPEH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS7-TLE
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Introduction to TLE', 'Technology and Livelihood Education prepares students with practical skills for work and daily life.', '<p>Technology and Livelihood Education prepares students with practical skills for work and daily life.</p><h3>Key points</h3><ul><li>TLE includes Home Economics, Industrial Arts, Agri-Fishery Arts, and ICT.</li><li>These skills can lead to livelihood and future careers.</li><li>Students explore different areas before choosing a specialization.</li></ul><h3>Try this</h3><p>Which TLE area interests you the most? Explain why.</p>', 1 FROM courses c
WHERE c.course_code = 'HS7-TLE' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Workplace Safety', 'Safety in the workplace prevents accidents and injuries.', '<p>Safety in the workplace prevents accidents and injuries.</p><h3>Key points</h3><ul><li>Wear personal protective equipment like gloves, goggles, and aprons.</li><li>Keep work areas clean and organized.</li><li>Know where the first aid kit and fire extinguisher are.</li></ul><h3>Try this</h3><p>List five safety rules for working in the TLE laboratory.</p>', 1 FROM courses c
WHERE c.course_code = 'HS7-TLE' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS8-FIL
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Karunungang-bayan', 'Ang karunungang-bayan ay mga pahayag na nagmula sa ating mga ninuno at nagtataglay ng aral at talino ng lahi.', '<p>Ang karunungang-bayan ay mga pahayag na nagmula sa ating mga ninuno at nagtataglay ng aral at talino ng lahi.</p><h3>Mahahalagang Punto</h3><ul><li>Kabilang dito ang salawikain, sawikain, kasabihan, at bugtong.</li><li>Ang salawikain ay may malalim na aral, gaya ng &quot;Kung ano ang itinanim, siya ring aanihin.&quot;</li><li>Ang bugtong ay palaisipang may nakatagong sagot.</li></ul><h3>Subukan Mo</h3><p>Magtala ng tatlong salawikain at ipaliwanag ang kahulugan ng bawat isa.</p>', 1 FROM courses c
WHERE c.course_code = 'HS8-FIL' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Florante at Laura: Ang May-akda at Kaligiran', 'Isinulat ni Francisco Balagtas ang Florante at Laura habang siya ay nakakulong, at itinuturing itong obra maestra ng panitikang Pilipino.', '<p>Isinulat ni Francisco Balagtas ang Florante at Laura habang siya ay nakakulong, at itinuturing itong obra maestra ng panitikang Pilipino.</p><h3>Mahahalagang Punto</h3><ul><li>Ito ay awit na may labindalawang pantig bawat taludtod.</li><li>Ginamit ng akda ang Albanya bilang tagpuan upang maitago ang puna sa pamahalaang Kastila.</li><li>Inialay ito ni Balagtas kay &quot;Selya&quot; na pinaniniwalaang si Maria Asuncion Rivera.</li></ul><h3>Subukan Mo</h3><p>Bakit sa ibang bansa itinakda ni Balagtas ang kuwento?</p>', 1 FROM courses c
WHERE c.course_code = 'HS8-FIL' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS8-ENGLISH
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Afro-Asian Literature', 'Afro-Asian literature brings us stories, poems, and wisdom from the cultures of Africa and Asia.', '<p>Afro-Asian literature brings us stories, poems, and wisdom from the cultures of Africa and Asia.</p><h3>Key points</h3><ul><li>Many works reflect respect for elders, community, and tradition.</li><li>Forms include fables, epics, haiku, and folk tales.</li><li>Reading them helps us understand cultures different from our own.</li></ul><h3>Try this</h3><p>Compare a value found in an African folk tale with a Filipino value.</p>', 1 FROM courses c
WHERE c.course_code = 'HS8-ENGLISH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Context Clues', 'Context clues are hints in a sentence or passage that help you figure out the meaning of an unfamiliar word.', '<p>Context clues are hints in a sentence or passage that help you figure out the meaning of an unfamiliar word.</p><h3>Key points</h3><ul><li>Definition clues explain the word directly.</li><li>Synonym and antonym clues give a word with a similar or opposite meaning.</li><li>Example clues show the meaning through examples.</li></ul><h3>Try this</h3><p>Find three unfamiliar words in a story and use context clues to define them.</p>', 1 FROM courses c
WHERE c.course_code = 'HS8-ENGLISH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS8-MATH
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Factoring Polynomials', 'Factoring writes a polynomial as a product of simpler expressions.', '<p>Factoring writes a polynomial as a product of simpler expressions.</p><h3>Key points</h3><ul><li>Always look first for a common monomial factor: 6x + 9 = 3(2x + 3).</li><li>Difference of two squares: a² − b² = (a + b)(a − b).</li><li>Trinomials like x² + 5x + 6 factor into (x + 2)(x + 3).</li></ul><h3>Try this</h3><p>Factor x² − 49 and x² + 7x + 12.</p>', 1 FROM courses c
WHERE c.course_code = 'HS8-MATH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Rational Algebraic Expressions', 'A rational algebraic expression is a fraction whose numerator and denominator are polynomials.', '<p>A rational algebraic expression is a fraction whose numerator and denominator are polynomials.</p><h3>Key points</h3><ul><li>Simplify by factoring and canceling common factors.</li><li>The denominator can never equal zero.</li><li>Add or subtract by finding a common denominator.</li></ul><h3>Try this</h3><p>Simplify (x² − 9) / (x + 3).</p>', 1 FROM courses c
WHERE c.course_code = 'HS8-MATH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS8-SCI
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Forces and Newton''s Laws', 'Newton''s laws of motion describe how forces affect the movement of objects.', '<p>Newton&#x27;s laws of motion describe how forces affect the movement of objects.</p><h3>Key points</h3><ul><li>First law (inertia): an object stays at rest or in motion unless acted on by a force.</li><li>Second law: force = mass × acceleration.</li><li>Third law: for every action there is an equal and opposite reaction.</li></ul><h3>Try this</h3><p>Give an everyday example of each of Newton&#x27;s three laws.</p>', 1 FROM courses c
WHERE c.course_code = 'HS8-SCI' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Work, Power, and Energy', 'Work is done when a force moves an object, and energy is the ability to do work.', '<p>Work is done when a force moves an object, and energy is the ability to do work.</p><h3>Key points</h3><ul><li>Work = force × distance.</li><li>Potential energy is stored energy; kinetic energy is energy of motion.</li><li>Energy cannot be created or destroyed, only changed from one form to another.</li></ul><h3>Try this</h3><p>Explain the energy changes in a swinging pendulum.</p>', 1 FROM courses c
WHERE c.course_code = 'HS8-SCI' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS8-AP
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Heograpiya ng Daigdig', 'Pinag-aaralan ng heograpiya ang katangiang pisikal ng daigdig at ang ugnayan nito sa tao.', '<p>Pinag-aaralan ng heograpiya ang katangiang pisikal ng daigdig at ang ugnayan nito sa tao.</p><h3>Mahahalagang Punto</h3><ul><li>May pitong kontinente at limang karagatan ang daigdig.</li><li>Ginagamit ang latitude at longitude upang matukoy ang lokasyon.</li><li>Ang klima at anyong-lupa ay nakaaapekto sa pamumuhay ng tao.</li></ul><h3>Subukan Mo</h3><p>Hanapin sa globo ang latitude at longitude ng Pilipinas.</p>', 1 FROM courses c
WHERE c.course_code = 'HS8-AP' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Mga Sinaunang Kabihasnan ng Daigdig', 'Ang mga kabihasnang Griyego at Romano ay nag-iwan ng malaking ambag sa makabagong daigdig.', '<p>Ang mga kabihasnang Griyego at Romano ay nag-iwan ng malaking ambag sa makabagong daigdig.</p><h3>Mahahalagang Punto</h3><ul><li>Sa Gresya nagmula ang demokrasya at pilosopiya.</li><li>Kilala ang Roma sa batas, inhinyeriya, at pamahalaan.</li><li>Mahalaga rin ang kabihasnan ng Ehipto, gaya ng mga piramide.</li></ul><h3>Subukan Mo</h3><p>Magbigay ng tatlong ambag ng mga Griyego na ginagamit pa natin ngayon.</p>', 1 FROM courses c
WHERE c.course_code = 'HS8-AP' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS8-ESP
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Ang Pamilya', 'Ang pamilya ang pinakapangunahing yunit ng lipunan at unang paaralan ng pagmamahal.', '<p>Ang pamilya ang pinakapangunahing yunit ng lipunan at unang paaralan ng pagmamahal.</p><h3>Mahahalagang Punto</h3><ul><li>Dito natututuhan ang pagpapahalaga, disiplina, at pananampalataya.</li><li>May iba&#x27;t ibang anyo ang pamilya, ngunit pareho ang layunin nitong mag-aruga.</li><li>Bawat kasapi ay may tungkulin sa pamilya.</li></ul><h3>Subukan Mo</h3><p>Isulat ang iyong mga tungkulin sa inyong tahanan.</p>', 1 FROM courses c
WHERE c.course_code = 'HS8-ESP' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Komunikasyon sa Pamilya', 'Ang maayos na komunikasyon ay nagpapatibay sa ugnayan ng pamilya.', '<p>Ang maayos na komunikasyon ay nagpapatibay sa ugnayan ng pamilya.</p><h3>Mahahalagang Punto</h3><ul><li>Makinig nang mabuti bago magsalita.</li><li>Ipahayag ang damdamin nang may paggalang.</li><li>Iwasan ang pagsigaw at paninisi kapag may hindi pagkakaunawaan.</li></ul><h3>Subukan Mo</h3><p>Magsanay ng isang mahinahong pag-uusap sa isang kasapi ng pamilya at isulat ang naging karanasan.</p>', 1 FROM courses c
WHERE c.course_code = 'HS8-ESP' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS8-MAPEH
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Music: Music of Southeast Asia', 'Southeast Asian countries share musical traditions that use gongs and percussion.', '<p>Southeast Asian countries share musical traditions that use gongs and percussion.</p><h3>Key points</h3><ul><li>Indonesia is known for the gamelan ensemble.</li><li>Thailand uses the piphat ensemble in ceremonies.</li><li>The Philippine kulintang is similar to these gong ensembles.</li></ul><h3>Try this</h3><p>Compare the sound of a gamelan and a kulintang after listening to recordings.</p>', 1 FROM courses c
WHERE c.course_code = 'HS8-MAPEH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Arts: Arts of Southeast Asia', 'Southeast Asian arts include textiles, sculpture, and puppetry.', '<p>Southeast Asian arts include textiles, sculpture, and puppetry.</p><h3>Key points</h3><ul><li>Indonesia is known for batik, a wax-resist dyeing technique.</li><li>Wayang kulit is shadow puppetry from Java.</li><li>Many artworks have religious and cultural meanings.</li></ul><h3>Try this</h3><p>Create a simple batik-style design using crayons and watercolor.</p>', 1 FROM courses c
WHERE c.course_code = 'HS8-MAPEH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS8-TLE
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Entrepreneurship Basics', 'Entrepreneurship is the process of starting and managing a business to earn profit.', '<p>Entrepreneurship is the process of starting and managing a business to earn profit.</p><h3>Key points</h3><ul><li>An entrepreneur identifies needs and creates products or services.</li><li>Good entrepreneurs are creative, hardworking, and willing to take risks.</li><li>Small businesses support local communities.</li></ul><h3>Try this</h3><p>Think of a small business that could succeed in your barangay.</p>', 1 FROM courses c
WHERE c.course_code = 'HS8-TLE' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Food Preparation', 'Proper food preparation keeps food safe, tasty, and nutritious.', '<p>Proper food preparation keeps food safe, tasty, and nutritious.</p><h3>Key points</h3><ul><li>Wash hands, utensils, and ingredients before cooking.</li><li>Separate raw and cooked food to avoid contamination.</li><li>Cook food to the right temperature.</li></ul><h3>Try this</h3><p>Prepare a simple vegetable dish and explain the steps you followed.</p>', 1 FROM courses c
WHERE c.course_code = 'HS8-TLE' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS9-FIL
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Panitikang Asyano', 'Ang panitikang Asyano ay mga akda mula sa iba''t ibang bansa sa Asya na nagpapakita ng kultura at pinahahalagahan ng mga Asyano.', '<p>Ang panitikang Asyano ay mga akda mula sa iba&#x27;t ibang bansa sa Asya na nagpapakita ng kultura at pinahahalagahan ng mga Asyano.</p><h3>Mahahalagang Punto</h3><ul><li>Kabilang dito ang maikling kuwento, tula, at dula mula sa Silangan at Timog-Silangang Asya.</li><li>Ang tanka at haiku ay maiikling tulang nagmula sa Japan.</li><li>Madalas itong tumatalakay sa pamilya, kalikasan, at karangalan.</li></ul><h3>Subukan Mo</h3><p>Sumulat ng isang haiku (5-7-5 na pantig) tungkol sa kalikasan.</p>', 1 FROM courses c
WHERE c.course_code = 'HS9-FIL' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Noli Me Tangere: Kaligiran ng Akda', 'Isinulat ni Dr. Jose Rizal ang Noli Me Tangere noong 1887 upang ilantad ang mga sakit ng lipunan sa ilalim ng pamahalaang Kastila.', '<p>Isinulat ni Dr. Jose Rizal ang Noli Me Tangere noong 1887 upang ilantad ang mga sakit ng lipunan sa ilalim ng pamahalaang Kastila.</p><h3>Mahahalagang Punto</h3><ul><li>Ang pamagat ay Latin na nangangahulugang &quot;Huwag Mo Akong Salingin.&quot;</li><li>Inspirasyon ni Rizal ang nobelang Uncle Tom&#x27;s Cabin.</li><li>Inialay niya ito sa Inang Bayan.</li></ul><h3>Subukan Mo</h3><p>Ano ang &quot;kanser ng lipunan&quot; na tinutukoy ni Rizal, at mayroon pa ba nito ngayon?</p>', 1 FROM courses c
WHERE c.course_code = 'HS9-FIL' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS9-ENGLISH
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Anglo-American Literature', 'Anglo-American literature includes works from Britain and the United States that shaped modern English writing.', '<p>Anglo-American literature includes works from Britain and the United States that shaped modern English writing.</p><h3>Key points</h3><ul><li>Writers include William Shakespeare, Edgar Allan Poe, and Robert Frost.</li><li>Many works explore themes of identity, freedom, and human nature.</li><li>Poetry, drama, and short stories are common forms.</li></ul><h3>Try this</h3><p>Read Robert Frost&#x27;s &quot;The Road Not Taken&quot; and explain the choice the speaker made.</p>', 1 FROM courses c
WHERE c.course_code = 'HS9-ENGLISH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Elements of Drama', 'Drama is literature written to be performed on stage, told through dialogue and action.', '<p>Drama is literature written to be performed on stage, told through dialogue and action.</p><h3>Key points</h3><ul><li>A play is divided into acts and scenes.</li><li>Stage directions tell actors how to move and speak.</li><li>Conflict drives the plot toward the climax and resolution.</li></ul><h3>Try this</h3><p>Write a short scene with two characters who disagree about a decision.</p>', 1 FROM courses c
WHERE c.course_code = 'HS9-ENGLISH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS9-MATH
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Quadratic Equations', 'A quadratic equation has the form ax² + bx + c = 0, where a is not zero.', '<p>A quadratic equation has the form ax² + bx + c = 0, where a is not zero.</p><h3>Key points</h3><ul><li>Solve by factoring, completing the square, or the quadratic formula.</li><li>Quadratic formula: x = [−b ± √(b² − 4ac)] / 2a.</li><li>The discriminant b² − 4ac tells how many real roots there are.</li></ul><h3>Try this</h3><p>Solve x² − 5x + 6 = 0 by factoring.</p>', 1 FROM courses c
WHERE c.course_code = 'HS9-MATH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Quadratic Functions', 'A quadratic function, y = ax² + bx + c, has a U-shaped graph called a parabola.', '<p>A quadratic function, y = ax² + bx + c, has a U-shaped graph called a parabola.</p><h3>Key points</h3><ul><li>If a is positive the parabola opens upward; if negative, downward.</li><li>The vertex is the highest or lowest point.</li><li>The axis of symmetry passes through the vertex.</li></ul><h3>Try this</h3><p>Find the vertex of y = x² − 4x + 3.</p>', 1 FROM courses c
WHERE c.course_code = 'HS9-MATH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS9-SCI
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Respiratory and Circulatory Systems', 'These two systems work together to deliver oxygen to the body and remove carbon dioxide.', '<p>These two systems work together to deliver oxygen to the body and remove carbon dioxide.</p><h3>Key points</h3><ul><li>The lungs take in oxygen and release carbon dioxide.</li><li>The heart pumps blood through arteries, veins, and capillaries.</li><li>Exercise and a healthy diet keep both systems strong.</li></ul><h3>Try this</h3><p>Measure your pulse before and after jumping for one minute. Explain the change.</p>', 1 FROM courses c
WHERE c.course_code = 'HS9-SCI' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Heredity', 'Heredity is the passing of traits from parents to offspring through genes.', '<p>Heredity is the passing of traits from parents to offspring through genes.</p><h3>Key points</h3><ul><li>Genes come in pairs called alleles; dominant alleles hide recessive ones.</li><li>Gregor Mendel is known as the father of genetics.</li><li>A Punnett square predicts the possible traits of offspring.</li></ul><h3>Try this</h3><p>Use a Punnett square to cross two parents who are both Tt for height.</p>', 1 FROM courses c
WHERE c.course_code = 'HS9-SCI' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS9-AP
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Kahulugan ng Ekonomiks', 'Ang ekonomiks ay pag-aaral kung paano ginagamit ang limitadong yaman upang matugunan ang walang katapusang pangangailangan.', '<p>Ang ekonomiks ay pag-aaral kung paano ginagamit ang limitadong yaman upang matugunan ang walang katapusang pangangailangan.</p><h3>Mahahalagang Punto</h3><ul><li>Kakapusan ang pangunahing suliranin ng ekonomiks.</li><li>Kailangang pumili, at ang isinakripisyong opsyon ay tinatawag na opportunity cost.</li><li>Mahalaga ito sa matalinong pagdedesisyon sa araw-araw.</li></ul><h3>Subukan Mo</h3><p>Magbigay ng halimbawa ng opportunity cost sa iyong buhay bilang estudyante.</p>', 1 FROM courses c
WHERE c.course_code = 'HS9-AP' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Pangangailangan at Kagustuhan', 'Magkaiba ang pangangailangan at kagustuhan, at mahalagang malaman ang pagkakaiba nila.', '<p>Magkaiba ang pangangailangan at kagustuhan, at mahalagang malaman ang pagkakaiba nila.</p><h3>Mahahalagang Punto</h3><ul><li>Ang pangangailangan ay mahalaga upang mabuhay, gaya ng pagkain at tirahan.</li><li>Ang kagustuhan ay nagpapaginhawa sa buhay ngunit hindi kailangan upang mabuhay.</li><li>Ipinaliwanag ni Abraham Maslow ang hirarkiya ng pangangailangan.</li></ul><h3>Subukan Mo</h3><p>Itala ang iyong mga ginastos sa isang linggo at uriin kung pangangailangan o kagustuhan.</p>', 1 FROM courses c
WHERE c.course_code = 'HS9-AP' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS9-ESP
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Lipunan at Kabutihang Panlahat', 'Ang kabutihang panlahat ay kabutihan na nakatutulong sa lahat ng kasapi ng lipunan.', '<p>Ang kabutihang panlahat ay kabutihan na nakatutulong sa lahat ng kasapi ng lipunan.</p><h3>Mahahalagang Punto</h3><ul><li>Ang lipunan ay binubuo ng mga taong may iisang layunin.</li><li>Kailangan ang paggalang, katarungan, at kapayapaan upang makamit ito.</li><li>Bawat isa ay may papel sa pagbuo ng mabuting lipunan.</li></ul><h3>Subukan Mo</h3><p>Magbigay ng isang proyekto sa paaralan na makatutulong sa kabutihang panlahat.</p>', 1 FROM courses c
WHERE c.course_code = 'HS9-ESP' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Karapatan at Tungkulin', 'Bawat karapatan ay may kaakibat na tungkulin.', '<p>Bawat karapatan ay may kaakibat na tungkulin.</p><h3>Mahahalagang Punto</h3><ul><li>Karapatan ang mag-aral; tungkulin ang mag-aral nang mabuti.</li><li>Karapatan ang malayang pagpapahayag; tungkulin ang magsalita nang may katotohanan.</li><li>Ang paggalang sa karapatan ng iba ay tungkulin din natin.</li></ul><h3>Subukan Mo</h3><p>Ipares ang tatlong karapatan sa kaakibat nitong tungkulin.</p>', 1 FROM courses c
WHERE c.course_code = 'HS9-ESP' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS9-MAPEH
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Music: Music of the Medieval to Classical Periods', 'Western music changed greatly from the Medieval period to the Classical period.', '<p>Western music changed greatly from the Medieval period to the Classical period.</p><h3>Key points</h3><ul><li>Medieval: Gregorian chant was sung in churches.</li><li>Baroque: composers like Bach used rich ornamentation.</li><li>Classical: composers like Mozart and Beethoven valued balance and clarity.</li></ul><h3>Try this</h3><p>Listen to a piece by Mozart and describe its mood and tempo.</p>', 1 FROM courses c
WHERE c.course_code = 'HS9-MAPEH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Arts: Western Classical Art', 'Western art developed through major periods, each with its own style.', '<p>Western art developed through major periods, each with its own style.</p><h3>Key points</h3><ul><li>Renaissance artists like Leonardo da Vinci focused on realism and perspective.</li><li>Baroque art used drama and strong contrasts of light and dark.</li><li>These works still influence art today.</li></ul><h3>Try this</h3><p>Describe the Mona Lisa and explain why it is famous.</p>', 1 FROM courses c
WHERE c.course_code = 'HS9-MAPEH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS9-TLE
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Choosing a TLE Specialization', 'In Grade 9, students focus on one TLE specialization to build deeper skills.', '<p>In Grade 9, students focus on one TLE specialization to build deeper skills.</p><h3>Key points</h3><ul><li>Options may include Cookery, Dressmaking, Electrical Installation, and Computer Systems Servicing.</li><li>Choose based on interest, skills, and future plans.</li><li>Specializations can lead to TESDA National Certificates.</li></ul><h3>Try this</h3><p>Research one TESDA qualification related to your chosen specialization.</p>', 1 FROM courses c
WHERE c.course_code = 'HS9-TLE' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Occupational Health and Safety', 'Occupational health and safety protects workers from hazards on the job.', '<p>Occupational health and safety protects workers from hazards on the job.</p><h3>Key points</h3><ul><li>Hazards can be physical, chemical, biological, or ergonomic.</li><li>Identify risks before starting a task.</li><li>Report unsafe conditions to a supervisor.</li></ul><h3>Try this</h3><p>Identify three hazards in your TLE workplace and how to control them.</p>', 1 FROM courses c
WHERE c.course_code = 'HS9-TLE' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS10-FIL
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Mitolohiya', 'Ang mitolohiya ay koleksyon ng mga kuwento tungkol sa mga diyos, bayani, at pinagmulan ng daigdig.', '<p>Ang mitolohiya ay koleksyon ng mga kuwento tungkol sa mga diyos, bayani, at pinagmulan ng daigdig.</p><h3>Mahahalagang Punto</h3><ul><li>Kilala ang mitolohiyang Griyego at Romano, gaya ng kuwento nina Zeus at Jupiter.</li><li>May sariling mitolohiya rin ang Pilipinas, gaya ni Bathala at Malakas at Maganda.</li><li>Ipinapaliwanag nito ang mga pangyayari sa kalikasan na hindi pa noon naiintindihan.</li></ul><h3>Subukan Mo</h3><p>Ihambing ang isang diyos ng mitolohiyang Griyego sa isang diyos ng mitolohiyang Pilipino.</p>', 1 FROM courses c
WHERE c.course_code = 'HS10-FIL' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Panitikang Pandaigdig', 'Sa pagbasa ng panitikan mula sa iba''t ibang bansa, nauunawaan natin ang karanasan at kultura ng ibang tao.', '<p>Sa pagbasa ng panitikan mula sa iba&#x27;t ibang bansa, nauunawaan natin ang karanasan at kultura ng ibang tao.</p><h3>Mahahalagang Punto</h3><ul><li>Kabilang dito ang mga akda mula sa Mediterranean, Europa, Africa, at Amerika.</li><li>Ang parabula ay maikling kuwentong may aral na kadalasang hango sa Bibliya.</li><li>Ang epiko ay mahabang tula tungkol sa kabayanihan ng pangunahing tauhan.</li></ul><h3>Subukan Mo</h3><p>Pumili ng isang akdang pandaigdig at ibuod ito sa limang pangungusap.</p>', 1 FROM courses c
WHERE c.course_code = 'HS10-FIL' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS10-ENGLISH
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'World Literature', 'World literature lets us explore universal human experiences through works from many countries.', '<p>World literature lets us explore universal human experiences through works from many countries.</p><h3>Key points</h3><ul><li>Common themes include love, sacrifice, justice, and identity.</li><li>Translations let us read works originally written in other languages.</li><li>Context, such as history and culture, shapes a work&#x27;s meaning.</li></ul><h3>Try this</h3><p>Choose a world literature piece and explain a theme that is still relevant today.</p>', 1 FROM courses c
WHERE c.course_code = 'HS10-ENGLISH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Argumentative Writing', 'An argumentative essay takes a clear position and supports it with reasons and evidence.', '<p>An argumentative essay takes a clear position and supports it with reasons and evidence.</p><h3>Key points</h3><ul><li>The claim states your position on an issue.</li><li>Evidence includes facts, statistics, and expert opinions.</li><li>A strong argument also addresses and answers the opposing view.</li></ul><h3>Try this</h3><p>Write a claim and three reasons on whether students should wear uniforms.</p>', 1 FROM courses c
WHERE c.course_code = 'HS10-ENGLISH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS10-MATH
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Sequences', 'A sequence is an ordered list of numbers that follows a pattern.', '<p>A sequence is an ordered list of numbers that follows a pattern.</p><h3>Key points</h3><ul><li>Arithmetic sequence: add the same number each time; nth term = a₁ + (n − 1)d.</li><li>Geometric sequence: multiply by the same number each time; nth term = a₁ · rⁿ⁻¹.</li><li>Series is the sum of the terms of a sequence.</li></ul><h3>Try this</h3><p>Find the 10th term of 3, 7, 11, 15, ...</p>', 1 FROM courses c
WHERE c.course_code = 'HS10-MATH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Polynomials and Polynomial Equations', 'A polynomial is an expression with variables raised to whole-number exponents.', '<p>A polynomial is an expression with variables raised to whole-number exponents.</p><h3>Key points</h3><ul><li>Use synthetic division to divide a polynomial by (x − c).</li><li>The Remainder Theorem: P(c) is the remainder when P(x) is divided by (x − c).</li><li>The Factor Theorem: (x − c) is a factor if P(c) = 0.</li></ul><h3>Try this</h3><p>Use the Remainder Theorem to find the remainder when x³ − 2x + 4 is divided by (x − 2).</p>', 1 FROM courses c
WHERE c.course_code = 'HS10-MATH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS10-SCI
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Plate Tectonics', 'The Earth''s outer layer is divided into plates that move slowly over time.', '<p>The Earth&#x27;s outer layer is divided into plates that move slowly over time.</p><h3>Key points</h3><ul><li>Plates can move apart (divergent), collide (convergent), or slide past each other (transform).</li><li>Plate movement causes earthquakes, volcanoes, and mountain formation.</li><li>The Philippine Trench is formed by a convergent boundary.</li></ul><h3>Try this</h3><p>Explain how the Himalayas were formed.</p>', 1 FROM courses c
WHERE c.course_code = 'HS10-SCI' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'The Electromagnetic Spectrum', 'The electromagnetic spectrum is the full range of light waves, from radio waves to gamma rays.', '<p>The electromagnetic spectrum is the full range of light waves, from radio waves to gamma rays.</p><h3>Key points</h3><ul><li>Order from longest to shortest wavelength: radio, microwave, infrared, visible, ultraviolet, X-ray, gamma.</li><li>Shorter wavelengths carry more energy.</li><li>Each type has uses, such as microwaves for cooking and X-rays for medicine.</li></ul><h3>Try this</h3><p>List one use and one risk of ultraviolet radiation.</p>', 1 FROM courses c
WHERE c.course_code = 'HS10-SCI' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS10-AP
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Kontemporaryong Isyu', 'Ang kontemporaryong isyu ay mga usaping napapanahon na nakaaapekto sa lipunan.', '<p>Ang kontemporaryong isyu ay mga usaping napapanahon na nakaaapekto sa lipunan.</p><h3>Mahahalagang Punto</h3><ul><li>Maaari itong pangkapaligiran, pang-ekonomiya, pampulitika, o panlipunan.</li><li>Mahalaga ang pagsusuri ng sanggunian upang maiwasan ang maling impormasyon.</li><li>Bilang mamamayan, may tungkulin tayong makialam nang may kaalaman.</li></ul><h3>Subukan Mo</h3><p>Pumili ng isang balita ngayong linggo at tukuyin kung anong uri ng isyu ito.</p>', 1 FROM courses c
WHERE c.course_code = 'HS10-AP' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Mga Isyung Pangkapaligiran', 'Kinakaharap ng Pilipinas ang maraming suliraning pangkapaligiran.', '<p>Kinakaharap ng Pilipinas ang maraming suliraning pangkapaligiran.</p><h3>Mahahalagang Punto</h3><ul><li>Kabilang dito ang climate change, polusyon, at pagkasira ng kagubatan.</li><li>Ang Pilipinas ay isa sa mga bansang pinakaapektado ng kalamidad.</li><li>Mahalaga ang disaster risk reduction upang mabawasan ang pinsala.</li></ul><h3>Subukan Mo</h3><p>Gumawa ng plano ng inyong pamilya bago, habang, at pagkatapos ng bagyo.</p>', 1 FROM courses c
WHERE c.course_code = 'HS10-AP' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS10-ESP
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Ang Mataas na Gamit ng Isip at Kilos-loob', 'Ginagamit ang isip at kilos-loob upang hanapin ang katotohanan at piliin ang mabuti.', '<p>Ginagamit ang isip at kilos-loob upang hanapin ang katotohanan at piliin ang mabuti.</p><h3>Mahahalagang Punto</h3><ul><li>Ang isip ay may kakayahang umunawa at magsuri.</li><li>Ang kilos-loob ay malayang pumipili ng mabuti.</li><li>Ang tao ay may pananagutan sa bunga ng kanyang pasya.</li></ul><h3>Subukan Mo</h3><p>Isalaysay ang isang mahirap na pasya at kung paano mo ito pinag-isipan.</p>', 1 FROM courses c
WHERE c.course_code = 'HS10-ESP' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Paghubog ng Konsensya batay sa Likas na Batas Moral', 'Ang likas na batas moral ay ang pangkalahatang batas na nagsasabing gawin ang mabuti at iwasan ang masama.', '<p>Ang likas na batas moral ay ang pangkalahatang batas na nagsasabing gawin ang mabuti at iwasan ang masama.</p><h3>Mahahalagang Punto</h3><ul><li>Ito ay likas sa lahat ng tao, saan man at kailan man.</li><li>Ang konsensya ay gumagamit ng likas na batas moral bilang gabay.</li><li>Kailangan itong hubugin sa pamamagitan ng edukasyon at pagninilay.</li></ul><h3>Subukan Mo</h3><p>Magbigay ng halimbawa ng pasya na batay sa likas na batas moral.</p>', 1 FROM courses c
WHERE c.course_code = 'HS10-ESP' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS10-MAPEH
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Music: 20th Century and Contemporary Music', '20th-century music broke many old rules and explored new sounds.', '<p>20th-century music broke many old rules and explored new sounds.</p><h3>Key points</h3><ul><li>Impressionism, expressionism, and electronic music emerged.</li><li>Filipino composers like Ryan Cayabyab and Lucio San Pedro created modern Filipino music.</li><li>Popular music genres include rock, jazz, and hip-hop.</li></ul><h3>Try this</h3><p>Listen to a song by a Filipino composer and describe what makes it modern.</p>', 1 FROM courses c
WHERE c.course_code = 'HS10-MAPEH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Arts: Modern and Media-Based Arts', 'Modern art experiments with form, color, and new media.', '<p>Modern art experiments with form, color, and new media.</p><h3>Key points</h3><ul><li>Movements include cubism, surrealism, and pop art.</li><li>Digital art, photography, and film are media-based arts.</li><li>Filipino modernists include Vicente Manansala and Fernando Amorsolo.</li></ul><h3>Try this</h3><p>Create a simple digital poster about a school event.</p>', 1 FROM courses c
WHERE c.course_code = 'HS10-MAPEH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HS10-TLE
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Advanced Skills in Your Specialization', 'Grade 10 TLE deepens the skills of your chosen specialization.', '<p>Grade 10 TLE deepens the skills of your chosen specialization.</p><h3>Key points</h3><ul><li>Practice tasks based on competency standards.</li><li>Work independently and with less supervision.</li><li>Build a portfolio of finished work.</li></ul><h3>Try this</h3><p>Start a portfolio with photos and descriptions of your best TLE outputs.</p>', 1 FROM courses c
WHERE c.course_code = 'HS10-TLE' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Work Ethics', 'Work ethics are values that guide how a person behaves at work.', '<p>Work ethics are values that guide how a person behaves at work.</p><h3>Key points</h3><ul><li>Be punctual, honest, and responsible.</li><li>Respect co-workers and follow instructions.</li><li>Take pride in quality work.</li></ul><h3>Try this</h3><p>Write three work ethics you will practice during your TLE activities.</p>', 1 FROM courses c
WHERE c.course_code = 'HS10-TLE' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- SHS-GENMATH
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Functions and Their Graphs', 'A function is a relation in which each input has exactly one output.', '<p>A function is a relation in which each input has exactly one output.</p><h3>Key points</h3><ul><li>Functions can be shown as equations, tables, graphs, or mapping diagrams.</li><li>The vertical line test checks whether a graph is a function.</li><li>Function notation f(x) means &quot;the value of f at x.&quot;</li></ul><h3>Try this</h3><p>Evaluate f(x) = 2x² − 3x + 1 when x = 4.</p>', 1 FROM courses c
WHERE c.course_code = 'SHS-GENMATH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Rational Functions', 'A rational function is a ratio of two polynomial functions.', '<p>A rational function is a ratio of two polynomial functions.</p><h3>Key points</h3><ul><li>Vertical asymptotes occur where the denominator equals zero.</li><li>Horizontal asymptotes describe the graph&#x27;s behavior for very large x.</li><li>Rational functions model rates, averages, and work problems.</li></ul><h3>Try this</h3><p>Find the vertical asymptote of f(x) = (x + 1) / (x − 3).</p>', 1 FROM courses c
WHERE c.course_code = 'SHS-GENMATH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- SHS-STATS
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Random Variables', 'A random variable assigns a number to each outcome of a random experiment.', '<p>A random variable assigns a number to each outcome of a random experiment.</p><h3>Key points</h3><ul><li>Discrete random variables have countable values, like the number of heads.</li><li>Continuous random variables can take any value in a range, like height.</li><li>A probability distribution lists each value and its probability.</li></ul><h3>Try this</h3><p>Make a probability distribution for the number of heads when tossing two coins.</p>', 1 FROM courses c
WHERE c.course_code = 'SHS-STATS' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'The Normal Distribution', 'The normal distribution is a bell-shaped curve that describes many natural measurements.', '<p>The normal distribution is a bell-shaped curve that describes many natural measurements.</p><h3>Key points</h3><ul><li>It is symmetric around the mean.</li><li>About 68% of data falls within one standard deviation of the mean.</li><li>The z-score shows how many standard deviations a value is from the mean.</li></ul><h3>Try this</h3><p>A test has mean 75 and standard deviation 5. Find the z-score of a score of 85.</p>', 1 FROM courses c
WHERE c.course_code = 'SHS-STATS' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- SHS-EARTH
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'The Earth as a Planet', 'Earth is the only known planet with conditions that support life.', '<p>Earth is the only known planet with conditions that support life.</p><h3>Key points</h3><ul><li>Earth is the right distance from the Sun to have liquid water.</li><li>Its atmosphere protects life and regulates temperature.</li><li>Earth has four subsystems: geosphere, hydrosphere, atmosphere, and biosphere.</li></ul><h3>Try this</h3><p>Explain how two of Earth&#x27;s subsystems interact.</p>', 1 FROM courses c
WHERE c.course_code = 'SHS-EARTH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Minerals and Rocks', 'Rocks are made of minerals and are classified by how they form.', '<p>Rocks are made of minerals and are classified by how they form.</p><h3>Key points</h3><ul><li>Igneous rocks form from cooled magma or lava.</li><li>Sedimentary rocks form from compressed sediments.</li><li>Metamorphic rocks form when heat and pressure change existing rocks.</li></ul><h3>Try this</h3><p>Describe the rock cycle using a simple diagram.</p>', 1 FROM courses c
WHERE c.course_code = 'SHS-EARTH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- SHS-PHYSSCI
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Formation of Elements', 'Elements were formed in the Big Bang and inside stars.', '<p>Elements were formed in the Big Bang and inside stars.</p><h3>Key points</h3><ul><li>Hydrogen and helium formed shortly after the Big Bang.</li><li>Heavier elements formed through nuclear fusion in stars.</li><li>The heaviest elements formed in supernova explosions.</li></ul><h3>Try this</h3><p>Explain why we are said to be made of &quot;star stuff.&quot;</p>', 1 FROM courses c
WHERE c.course_code = 'SHS-PHYSSCI' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Properties of Matter', 'The properties of a substance depend on its structure and bonding.', '<p>The properties of a substance depend on its structure and bonding.</p><h3>Key points</h3><ul><li>Polarity affects how substances mix, like oil and water.</li><li>Intermolecular forces affect boiling and melting points.</li><li>Structure explains why diamonds are hard and graphite is soft.</li></ul><h3>Try this</h3><p>Explain why oil does not mix with water.</p>', 1 FROM courses c
WHERE c.course_code = 'SHS-PHYSSCI' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- SHS-ORALCOM
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Nature and Process of Communication', 'Communication is the process of sharing meaning through verbal and nonverbal messages.', '<p>Communication is the process of sharing meaning through verbal and nonverbal messages.</p><h3>Key points</h3><ul><li>Elements: speaker, message, channel, receiver, feedback, and context.</li><li>Communication can be verbal or nonverbal.</li><li>Noise or barriers can block understanding.</li></ul><h3>Try this</h3><p>Draw a communication model and label its parts.</p>', 1 FROM courses c
WHERE c.course_code = 'SHS-ORALCOM' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Communication Breakdown', 'A communication breakdown happens when the message is not understood as intended.', '<p>A communication breakdown happens when the message is not understood as intended.</p><h3>Key points</h3><ul><li>Barriers can be physical, psychological, semantic, or cultural.</li><li>Clear language and active listening reduce misunderstanding.</li><li>Feedback helps confirm the message was understood.</li></ul><h3>Try this</h3><p>Describe a misunderstanding you experienced and how it could have been avoided.</p>', 1 FROM courses c
WHERE c.course_code = 'SHS-ORALCOM' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- SHS-RECOM
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Reading and Thinking Strategies', 'Good readers use strategies to understand and remember what they read.', '<p>Good readers use strategies to understand and remember what they read.</p><h3>Key points</h3><ul><li>Preview the text by reading titles, headings, and summaries.</li><li>Ask questions and make predictions while reading.</li><li>Summarize the main ideas after reading.</li></ul><h3>Try this</h3><p>Use the SQ3R method on a chapter of any textbook.</p>', 1 FROM courses c
WHERE c.course_code = 'SHS-RECOM' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Patterns of Development', 'Writers organize ideas using patterns of development.', '<p>Writers organize ideas using patterns of development.</p><h3>Key points</h3><ul><li>Narration tells a story; description paints a picture with words.</li><li>Definition explains meaning; classification groups ideas into categories.</li><li>Comparison-contrast and cause-effect show relationships.</li></ul><h3>Try this</h3><p>Identify the pattern of development used in a short article.</p>', 1 FROM courses c
WHERE c.course_code = 'SHS-RECOM' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- SHS-EMPTECH
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'ICT in the Philippines', 'Information and communication technology has changed how Filipinos learn, work, and connect.', '<p>Information and communication technology has changed how Filipinos learn, work, and connect.</p><h3>Key points</h3><ul><li>The Philippines is a leader in business process outsourcing.</li><li>Web 1.0 was read-only, Web 2.0 is interactive, and Web 3.0 is the semantic web.</li><li>ICT trends include mobile technology, social media, and cloud computing.</li></ul><h3>Try this</h3><p>Give three ways ICT has changed your daily life.</p>', 1 FROM courses c
WHERE c.course_code = 'SHS-EMPTECH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Online Safety and Netiquette', 'Staying safe online protects your identity, privacy, and reputation.', '<p>Staying safe online protects your identity, privacy, and reputation.</p><h3>Key points</h3><ul><li>Use strong passwords and do not share personal information.</li><li>Beware of phishing, malware, and scams.</li><li>Netiquette means being respectful and responsible online.</li></ul><h3>Try this</h3><p>Create five rules of netiquette for your class group chat.</p>', 1 FROM courses c
WHERE c.course_code = 'SHS-EMPTECH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- SHS-APPECON
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Introduction to Applied Economics', 'Applied economics uses economic principles to solve real problems.', '<p>Applied economics uses economic principles to solve real problems.</p><h3>Key points</h3><ul><li>Scarcity forces individuals and societies to make choices.</li><li>Economics is divided into microeconomics and macroeconomics.</li><li>Economic decisions affect jobs, prices, and growth.</li></ul><h3>Try this</h3><p>Identify an economic problem in your community and its possible causes.</p>', 1 FROM courses c
WHERE c.course_code = 'SHS-APPECON' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Market Demand and Supply', 'Prices are determined by the interaction of demand and supply.', '<p>Prices are determined by the interaction of demand and supply.</p><h3>Key points</h3><ul><li>Changes in income, tastes, and prices of related goods shift demand.</li><li>Changes in technology and input costs shift supply.</li><li>Shortages and surpluses push prices toward equilibrium.</li></ul><h3>Try this</h3><p>Explain what happens to the price of rice after a bad harvest.</p>', 1 FROM courses c
WHERE c.course_code = 'SHS-APPECON' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- SHS-CREATIVEW
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Imagery and Figurative Language', 'Creative writers use imagery and figures of speech to make writing vivid.', '<p>Creative writers use imagery and figures of speech to make writing vivid.</p><h3>Key points</h3><ul><li>Imagery appeals to the five senses.</li><li>Simile compares using &quot;like&quot; or &quot;as&quot;; metaphor compares directly.</li><li>Personification gives human traits to non-human things.</li></ul><h3>Try this</h3><p>Write a paragraph describing the rain using at least three senses.</p>', 1 FROM courses c
WHERE c.course_code = 'SHS-CREATIVEW' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Writing Poetry', 'Poetry expresses ideas and emotions through carefully chosen words, sound, and form.', '<p>Poetry expresses ideas and emotions through carefully chosen words, sound, and form.</p><h3>Key points</h3><ul><li>Elements include rhyme, rhythm, meter, and stanza.</li><li>Free verse does not follow fixed rhyme or meter.</li><li>Every word should earn its place.</li></ul><h3>Try this</h3><p>Write a free-verse poem about your hometown.</p>', 1 FROM courses c
WHERE c.course_code = 'SHS-CREATIVEW' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- SHS-PR
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Nature of Research', 'Research is a systematic process of finding answers to questions.', '<p>Research is a systematic process of finding answers to questions.</p><h3>Key points</h3><ul><li>Quantitative research uses numbers and statistics.</li><li>Qualitative research explores experiences and meanings.</li><li>Good research is objective, ethical, and replicable.</li></ul><h3>Try this</h3><p>Give one research topic suited for quantitative and one for qualitative research.</p>', 1 FROM courses c
WHERE c.course_code = 'SHS-PR' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Identifying the Research Problem', 'A clear research problem guides the whole study.', '<p>A clear research problem guides the whole study.</p><h3>Key points</h3><ul><li>Choose a topic that is relevant, specific, and doable.</li><li>Write a research title and statement of the problem.</li><li>Define the scope and limitations of the study.</li></ul><h3>Try this</h3><p>Write a research title and three specific research questions.</p>', 1 FROM courses c
WHERE c.course_code = 'SHS-PR' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- SHS-PE
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Fitness and Healthy Lifestyle', 'A healthy lifestyle combines regular activity, good nutrition, and enough rest.', '<p>A healthy lifestyle combines regular activity, good nutrition, and enough rest.</p><h3>Key points</h3><ul><li>Adults and teens need regular moderate-to-vigorous activity.</li><li>Sleep and stress management affect fitness.</li><li>Set realistic and measurable fitness goals.</li></ul><h3>Try this</h3><p>Create a personal fitness plan for one week.</p>', 1 FROM courses c
WHERE c.course_code = 'SHS-PE' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Exercise Principles', 'Following exercise principles makes training safe and effective.', '<p>Following exercise principles makes training safe and effective.</p><h3>Key points</h3><ul><li>FITT: Frequency, Intensity, Time, and Type.</li><li>Progressive overload means gradually increasing difficulty.</li><li>Rest and recovery prevent injury.</li></ul><h3>Try this</h3><p>Apply the FITT principle to your favorite exercise.</p>', 1 FROM courses c
WHERE c.course_code = 'SHS-PE' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- SHS-WORKIMM
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Introduction to Work Immersion', 'Work immersion lets students experience real work in a chosen field.', '<p>Work immersion lets students experience real work in a chosen field.</p><h3>Key points</h3><ul><li>It connects classroom learning to actual work settings.</li><li>Students observe and perform tasks under supervision.</li><li>It helps students decide on future careers.</li></ul><h3>Try this</h3><p>List three things you hope to learn during work immersion.</p>', 1 FROM courses c
WHERE c.course_code = 'SHS-WORKIMM' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Workplace Behavior and Ethics', 'Proper behavior in the workplace shows professionalism.', '<p>Proper behavior in the workplace shows professionalism.</p><h3>Key points</h3><ul><li>Be punctual, wear proper attire, and follow company rules.</li><li>Respect supervisors and co-workers.</li><li>Keep company information confidential.</li></ul><h3>Try this</h3><p>Write a list of do&#x27;s and don&#x27;ts for your immersion site.</p>', 1 FROM courses c
WHERE c.course_code = 'SHS-WORKIMM' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- STEM-PRECAL
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Conic Sections', 'Conic sections are curves formed when a plane cuts a cone.', '<p>Conic sections are curves formed when a plane cuts a cone.</p><h3>Key points</h3><ul><li>The four conics are the circle, parabola, ellipse, and hyperbola.</li><li>Each has a standard equation and key parts like center and foci.</li><li>Conics appear in satellite dishes, orbits, and bridges.</li></ul><h3>Try this</h3><p>Find the center and radius of x² + y² − 6x + 4y − 3 = 0.</p>', 1 FROM courses c
WHERE c.course_code = 'STEM-PRECAL' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Systems of Nonlinear Equations', 'Nonlinear systems include at least one equation that is not linear.', '<p>Nonlinear systems include at least one equation that is not linear.</p><h3>Key points</h3><ul><li>Solve by substitution or elimination.</li><li>Solutions are the intersection points of the graphs.</li><li>A system can have zero, one, or many solutions.</li></ul><h3>Try this</h3><p>Solve the system y = x² and y = x + 2.</p>', 1 FROM courses c
WHERE c.course_code = 'STEM-PRECAL' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- STEM-BIO1
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Cell Theory and Cell Structure', 'The cell theory states that all living things are made of cells, the basic unit of life.', '<p>The cell theory states that all living things are made of cells, the basic unit of life.</p><h3>Key points</h3><ul><li>Prokaryotic cells have no nucleus; eukaryotic cells do.</li><li>Organelles have specific functions, like mitochondria for energy.</li><li>Cell structure is related to cell function.</li></ul><h3>Try this</h3><p>Make a table of five organelles and their functions.</p>', 1 FROM courses c
WHERE c.course_code = 'STEM-BIO1' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Cell Cycle and Division', 'Cells divide to grow, repair, and reproduce.', '<p>Cells divide to grow, repair, and reproduce.</p><h3>Key points</h3><ul><li>Mitosis produces two identical cells for growth and repair.</li><li>Meiosis produces four sex cells with half the chromosomes.</li><li>Errors in cell division can lead to diseases like cancer.</li></ul><h3>Try this</h3><p>Compare mitosis and meiosis using a Venn diagram.</p>', 1 FROM courses c
WHERE c.course_code = 'STEM-BIO1' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- STEM-CHEM1
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Matter and Its Properties', 'Matter is anything that has mass and takes up space.', '<p>Matter is anything that has mass and takes up space.</p><h3>Key points</h3><ul><li>Physical properties can be observed without changing the substance.</li><li>Chemical properties describe how a substance reacts.</li><li>Matter can be a pure substance or a mixture.</li></ul><h3>Try this</h3><p>Classify ten household items as elements, compounds, or mixtures.</p>', 1 FROM courses c
WHERE c.course_code = 'STEM-CHEM1' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Measurements', 'Chemistry relies on accurate and precise measurements.', '<p>Chemistry relies on accurate and precise measurements.</p><h3>Key points</h3><ul><li>The SI system is used in science.</li><li>Significant figures show the precision of a measurement.</li><li>Accuracy is closeness to the true value; precision is consistency.</li></ul><h3>Try this</h3><p>Round 0.0045670 to three significant figures.</p>', 1 FROM courses c
WHERE c.course_code = 'STEM-CHEM1' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- STEM-BIO2
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Genetics', 'Genetics studies how traits are inherited through genes.', '<p>Genetics studies how traits are inherited through genes.</p><h3>Key points</h3><ul><li>Mendel&#x27;s laws: segregation and independent assortment.</li><li>Some traits show incomplete dominance or codominance.</li><li>Sex-linked traits are carried on the X chromosome.</li></ul><h3>Try this</h3><p>Explain why color blindness is more common in males.</p>', 1 FROM courses c
WHERE c.course_code = 'STEM-BIO2' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Recombinant DNA and Biotechnology', 'Biotechnology uses living things to make useful products.', '<p>Biotechnology uses living things to make useful products.</p><h3>Key points</h3><ul><li>Recombinant DNA combines DNA from different sources.</li><li>Applications include insulin production and genetically modified crops.</li><li>Biotechnology raises ethical questions that need careful thought.</li></ul><h3>Try this</h3><p>Debate one benefit and one risk of genetically modified organisms.</p>', 1 FROM courses c
WHERE c.course_code = 'STEM-BIO2' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- STEM-CHEM2
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Intermolecular Forces', 'Intermolecular forces are attractions between molecules that affect physical properties.', '<p>Intermolecular forces are attractions between molecules that affect physical properties.</p><h3>Key points</h3><ul><li>Types include dispersion forces, dipole-dipole forces, and hydrogen bonds.</li><li>Stronger forces lead to higher boiling points.</li><li>Hydrogen bonding explains water&#x27;s unique properties.</li></ul><h3>Try this</h3><p>Explain why water has a higher boiling point than methane.</p>', 1 FROM courses c
WHERE c.course_code = 'STEM-CHEM2' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Solutions and Concentration', 'Concentration describes how much solute is in a solution.', '<p>Concentration describes how much solute is in a solution.</p><h3>Key points</h3><ul><li>Molarity (M) = moles of solute ÷ liters of solution.</li><li>Percent by mass and mole fraction are other units.</li><li>Colligative properties depend on the number of solute particles.</li></ul><h3>Try this</h3><p>Find the molarity of 58.5 g of NaCl dissolved in 1 liter of solution.</p>', 1 FROM courses c
WHERE c.course_code = 'STEM-CHEM2' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- STEM-PHYSICS
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Kinematics', 'Kinematics describes motion without considering its causes.', '<p>Kinematics describes motion without considering its causes.</p><h3>Key points</h3><ul><li>Displacement, velocity, and acceleration are vector quantities.</li><li>For constant acceleration: v = v₀ + at.</li><li>Free-falling objects accelerate at about 9.8 m/s².</li></ul><h3>Try this</h3><p>A ball is dropped from rest. How fast is it moving after 3 seconds?</p>', 1 FROM courses c
WHERE c.course_code = 'STEM-PHYSICS' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Newton''s Laws and Dynamics', 'Dynamics explains motion using forces.', '<p>Dynamics explains motion using forces.</p><h3>Key points</h3><ul><li>Draw free-body diagrams to show all forces on an object.</li><li>Net force = mass × acceleration.</li><li>Friction opposes motion between surfaces.</li></ul><h3>Try this</h3><p>Draw a free-body diagram of a box being pushed across the floor.</p>', 1 FROM courses c
WHERE c.course_code = 'STEM-PHYSICS' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- STEM-BASICCAL
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Limits', 'A limit describes the value a function approaches as the input gets close to a number.', '<p>A limit describes the value a function approaches as the input gets close to a number.</p><h3>Key points</h3><ul><li>Notation: lim (x→a) f(x) = L.</li><li>Limits can be found from tables, graphs, or algebra.</li><li>One-sided limits approach from the left or the right.</li></ul><h3>Try this</h3><p>Find lim (x→2) (x² − 4) / (x − 2).</p>', 1 FROM courses c
WHERE c.course_code = 'STEM-BASICCAL' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Continuity', 'A function is continuous at a point if its graph has no break there.', '<p>A function is continuous at a point if its graph has no break there.</p><h3>Key points</h3><ul><li>f(a) must exist.</li><li>The limit as x approaches a must exist.</li><li>The limit must equal f(a).</li></ul><h3>Try this</h3><p>Is f(x) = 1/x continuous at x = 0? Explain.</p>', 1 FROM courses c
WHERE c.course_code = 'STEM-BASICCAL' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- ABM-BUSMATH
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Fractions, Decimals, and Percent', 'Business calculations often require converting between fractions, decimals, and percent.', '<p>Business calculations often require converting between fractions, decimals, and percent.</p><h3>Key points</h3><ul><li>To change a decimal to percent, multiply by 100.</li><li>To change a percent to a decimal, divide by 100.</li><li>Percent is used in discounts, interest, and taxes.</li></ul><h3>Try this</h3><p>Convert 3/8 to a decimal and a percent.</p>', 1 FROM courses c
WHERE c.course_code = 'ABM-BUSMATH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Ratio and Proportion', 'Ratios compare quantities, and proportions show that two ratios are equal.', '<p>Ratios compare quantities, and proportions show that two ratios are equal.</p><h3>Key points</h3><ul><li>A ratio can be written as a:b or a/b.</li><li>Proportions solve problems like sharing profits.</li><li>Cross-multiplication solves for a missing term.</li></ul><h3>Try this</h3><p>Three partners share ₱90,000 in the ratio 2:3:4. How much does each get?</p>', 1 FROM courses c
WHERE c.course_code = 'ABM-BUSMATH' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- ABM-FABM1
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Introduction to Accounting', 'Accounting is the process of recording, classifying, and summarizing financial information.', '<p>Accounting is the process of recording, classifying, and summarizing financial information.</p><h3>Key points</h3><ul><li>Users include owners, investors, lenders, and the government.</li><li>Branches include financial, managerial, and tax accounting.</li><li>Accounting helps businesses make informed decisions.</li></ul><h3>Try this</h3><p>List three users of accounting information and what each needs to know.</p>', 1 FROM courses c
WHERE c.course_code = 'ABM-FABM1' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'The Accounting Equation', 'Every transaction keeps the accounting equation in balance.', '<p>Every transaction keeps the accounting equation in balance.</p><h3>Key points</h3><ul><li>Assets = Liabilities + Owner&#x27;s Equity.</li><li>Assets are resources owned; liabilities are debts owed.</li><li>Owner&#x27;s equity is the owner&#x27;s claim on the assets.</li></ul><h3>Try this</h3><p>If assets are ₱500,000 and liabilities are ₱200,000, find owner&#x27;s equity.</p>', 1 FROM courses c
WHERE c.course_code = 'ABM-FABM1' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- ABM-FABM2
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'The Statement of Financial Position', 'The statement of financial position shows a business''s assets, liabilities, and equity at a point in time.', '<p>The statement of financial position shows a business&#x27;s assets, liabilities, and equity at a point in time.</p><h3>Key points</h3><ul><li>It is also called the balance sheet.</li><li>Assets are classified as current or non-current.</li><li>It must always balance.</li></ul><h3>Try this</h3><p>Prepare a simple statement of financial position from given balances.</p>', 1 FROM courses c
WHERE c.course_code = 'ABM-FABM2' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'The Statement of Comprehensive Income', 'This statement shows a business''s income and expenses over a period.', '<p>This statement shows a business&#x27;s income and expenses over a period.</p><h3>Key points</h3><ul><li>Revenue minus expenses equals net income or net loss.</li><li>It shows how well the business performed.</li><li>Expenses include cost of sales and operating expenses.</li></ul><h3>Try this</h3><p>Compute net income if revenue is ₱250,000 and expenses are ₱180,000.</p>', 1 FROM courses c
WHERE c.course_code = 'ABM-FABM2' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- ABM-ORGMGT
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Nature and Concept of Management', 'Management is the process of reaching goals through people and resources.', '<p>Management is the process of reaching goals through people and resources.</p><h3>Key points</h3><ul><li>Functions of management: planning, organizing, leading, and controlling.</li><li>Managers work at top, middle, and lower levels.</li><li>Good management makes organizations efficient and effective.</li></ul><h3>Try this</h3><p>Interview a manager about their daily tasks and classify them by function.</p>', 1 FROM courses c
WHERE c.course_code = 'ABM-ORGMGT' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'The Business Environment', 'Businesses are affected by forces inside and outside the organization.', '<p>Businesses are affected by forces inside and outside the organization.</p><h3>Key points</h3><ul><li>The internal environment includes employees and culture.</li><li>The external environment includes customers, competitors, and the economy.</li><li>Filipino business culture values relationships and trust.</li></ul><h3>Try this</h3><p>Identify three external factors that affect a local sari-sari store.</p>', 1 FROM courses c
WHERE c.course_code = 'ABM-ORGMGT' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- ABM-MARKETING
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Introduction to Marketing', 'Marketing is the process of creating value for customers and building strong relationships.', '<p>Marketing is the process of creating value for customers and building strong relationships.</p><h3>Key points</h3><ul><li>Marketing focuses on customer needs and wants.</li><li>Good marketing creates satisfied, loyal customers.</li><li>Marketing includes research, product, pricing, distribution, and promotion.</li></ul><h3>Try this</h3><p>Explain how a local brand meets the needs of its customers.</p>', 1 FROM courses c
WHERE c.course_code = 'ABM-MARKETING' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'The Marketing Mix', 'The marketing mix is a set of tools used to reach the target market.', '<p>The marketing mix is a set of tools used to reach the target market.</p><h3>Key points</h3><ul><li>Product: what is being sold.</li><li>Price: how much customers pay.</li><li>Place and Promotion: where it is sold and how it is advertised.</li></ul><h3>Try this</h3><p>Analyze the 4Ps of your favorite snack product.</p>', 1 FROM courses c
WHERE c.course_code = 'ABM-MARKETING' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- ABM-BUSFIN
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Introduction to Business Finance', 'Business finance is about managing money to reach business goals.', '<p>Business finance is about managing money to reach business goals.</p><h3>Key points</h3><ul><li>Financial managers decide on investing, financing, and managing assets.</li><li>The goal is to maximize the value of the business.</li><li>Good financial decisions balance risk and return.</li></ul><h3>Try this</h3><p>List three financial decisions a small business owner makes.</p>', 1 FROM courses c
WHERE c.course_code = 'ABM-BUSFIN' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Financial Planning', 'Financial planning sets financial goals and prepares budgets to achieve them.', '<p>Financial planning sets financial goals and prepares budgets to achieve them.</p><h3>Key points</h3><ul><li>A budget estimates income and expenses.</li><li>Cash budgets help avoid running out of money.</li><li>Plans should be reviewed and updated.</li></ul><h3>Try this</h3><p>Prepare a one-month budget for a small food stall.</p>', 1 FROM courses c
WHERE c.course_code = 'ABM-BUSFIN' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- ABM-BUSETHICS
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Ethics in Business', 'Business ethics guides how companies and people behave in commerce.', '<p>Business ethics guides how companies and people behave in commerce.</p><h3>Key points</h3><ul><li>Ethics is about right and wrong conduct.</li><li>Ethical businesses earn trust and long-term success.</li><li>Unethical acts include fraud, bribery, and false advertising.</li></ul><h3>Try this</h3><p>Give an example of an unethical business practice and its effects.</p>', 1 FROM courses c
WHERE c.course_code = 'ABM-BUSETHICS' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Ethical Frameworks', 'Frameworks help people decide what is right.', '<p>Frameworks help people decide what is right.</p><h3>Key points</h3><ul><li>Utilitarianism chooses the greatest good for the greatest number.</li><li>Deontology focuses on duty and rules.</li><li>Virtue ethics focuses on good character.</li></ul><h3>Try this</h3><p>Apply two frameworks to a business dilemma.</p>', 1 FROM courses c
WHERE c.course_code = 'ABM-BUSETHICS' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HUMSS-INTRO
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Understanding Religion', 'Religion is a system of beliefs and practices related to the sacred.', '<p>Religion is a system of beliefs and practices related to the sacred.</p><h3>Key points</h3><ul><li>Religion, spirituality, and philosophy are related but different.</li><li>Religions shape culture, laws, and values.</li><li>Respect for others&#x27; beliefs promotes peace.</li></ul><h3>Try this</h3><p>Interview someone of a different faith about a belief they hold important.</p>', 1 FROM courses c
WHERE c.course_code = 'HUMSS-INTRO' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Judaism, Christianity, and Islam', 'These three monotheistic religions trace their roots to Abraham.', '<p>These three monotheistic religions trace their roots to Abraham.</p><h3>Key points</h3><ul><li>Judaism follows the Torah and believes in one God.</li><li>Christianity centers on Jesus Christ and the Bible.</li><li>Islam follows the Qur&#x27;an and the teachings of Prophet Muhammad.</li></ul><h3>Try this</h3><p>Compare one practice from each of the three religions.</p>', 1 FROM courses c
WHERE c.course_code = 'HUMSS-INTRO' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HUMSS-DISAS
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'The Social Sciences', 'The social sciences study human behavior and society.', '<p>The social sciences study human behavior and society.</p><h3>Key points</h3><ul><li>Disciplines include anthropology, economics, geography, history, political science, psychology, and sociology.</li><li>Each discipline asks different questions about people.</li><li>They help us understand and improve society.</li></ul><h3>Try this</h3><p>Choose one social problem and explain how two disciplines would study it.</p>', 1 FROM courses c
WHERE c.course_code = 'HUMSS-DISAS' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Structural-Functionalism', 'Structural-functionalism sees society as parts working together for stability.', '<p>Structural-functionalism sees society as parts working together for stability.</p><h3>Key points</h3><ul><li>Each institution, like family and school, has a function.</li><li>Problems arise when parts fail to work.</li><li>Émile Durkheim is a key thinker of this view.</li></ul><h3>Try this</h3><p>Explain the function of schools in society.</p>', 1 FROM courses c
WHERE c.course_code = 'HUMSS-DISAS' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HUMSS-PPG
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Politics and Governance', 'Politics is how power is gained and used, and governance is how a society is managed.', '<p>Politics is how power is gained and used, and governance is how a society is managed.</p><h3>Key points</h3><ul><li>Power, authority, and legitimacy are key concepts.</li><li>Good governance is transparent, accountable, and participatory.</li><li>Citizens play an active role in politics.</li></ul><h3>Try this</h3><p>Give an example of good governance in your local government.</p>', 1 FROM courses c
WHERE c.course_code = 'HUMSS-PPG' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Political Ideologies', 'Ideologies are sets of beliefs about how society should be organized.', '<p>Ideologies are sets of beliefs about how society should be organized.</p><h3>Key points</h3><ul><li>Liberalism values individual freedom.</li><li>Socialism values equality and shared ownership.</li><li>Conservatism values tradition and stability.</li></ul><h3>Try this</h3><p>Identify an ideology reflected in a current government policy.</p>', 1 FROM courses c
WHERE c.course_code = 'HUMSS-PPG' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HUMSS-CNF
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Introduction to Creative Nonfiction', 'Creative nonfiction tells true stories using literary techniques.', '<p>Creative nonfiction tells true stories using literary techniques.</p><h3>Key points</h3><ul><li>It must be factual and honest.</li><li>It uses scenes, dialogue, and imagery like fiction.</li><li>Forms include memoir, personal essay, and literary journalism.</li></ul><h3>Try this</h3><p>Read a personal essay and identify the literary techniques used.</p>', 1 FROM courses c
WHERE c.course_code = 'HUMSS-CNF' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'The Personal Essay', 'A personal essay reflects on an experience and what it means.', '<p>A personal essay reflects on an experience and what it means.</p><h3>Key points</h3><ul><li>It is written in the first person.</li><li>It combines storytelling and reflection.</li><li>A good essay connects personal experience to a larger idea.</li></ul><h3>Try this</h3><p>Write a personal essay about a place that shaped you.</p>', 1 FROM courses c
WHERE c.course_code = 'HUMSS-CNF' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HUMSS-TNCT
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Understanding Trends', 'A trend is a pattern of change that develops over time.', '<p>A trend is a pattern of change that develops over time.</p><h3>Key points</h3><ul><li>Trends differ from fads, which are short-lived.</li><li>Identify trends by looking at data over time.</li><li>Trends can be social, technological, or environmental.</li></ul><h3>Try this</h3><p>List three current trends among young people and explain whether each is a trend or a fad.</p>', 1 FROM courses c
WHERE c.course_code = 'HUMSS-TNCT' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Strategic Analysis and Intuitive Thinking', 'Critical thinking combines careful analysis with intuition.', '<p>Critical thinking combines careful analysis with intuition.</p><h3>Key points</h3><ul><li>Strategic analysis breaks a problem into parts.</li><li>Intuitive thinking uses experience and insight.</li><li>Both are needed to solve complex problems.</li></ul><h3>Try this</h3><p>Analyze a school problem using both strategic and intuitive thinking.</p>', 1 FROM courses c
WHERE c.course_code = 'HUMSS-TNCT' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HUMSS-APPSOCSCI
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Applying the Social Sciences', 'The social sciences provide tools to solve real problems in society.', '<p>The social sciences provide tools to solve real problems in society.</p><h3>Key points</h3><ul><li>Counseling, social work, and communication are applied fields.</li><li>Professionals use theory to guide practice.</li><li>They work in schools, hospitals, and communities.</li></ul><h3>Try this</h3><p>Identify a professional in your community who applies social science knowledge.</p>', 1 FROM courses c
WHERE c.course_code = 'HUMSS-APPSOCSCI' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Counseling', 'Counseling helps people understand and solve personal problems.', '<p>Counseling helps people understand and solve personal problems.</p><h3>Key points</h3><ul><li>Counselors listen, guide, and support clients.</li><li>Core values include respect, confidentiality, and empathy.</li><li>Guidance offices in schools provide counseling services.</li></ul><h3>Try this</h3><p>List the services offered by your school&#x27;s guidance office.</p>', 1 FROM courses c
WHERE c.course_code = 'HUMSS-APPSOCSCI' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- HUMSS-CESC
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Understanding Community', 'A community is a group of people who share a place, interests, or identity.', '<p>A community is a group of people who share a place, interests, or identity.</p><h3>Key points</h3><ul><li>Communities can be geographic or based on shared interests.</li><li>Every community has strengths and needs.</li><li>Knowing a community is the first step to helping it.</li></ul><h3>Try this</h3><p>Describe your community&#x27;s strengths and needs.</p>', 1 FROM courses c
WHERE c.course_code = 'HUMSS-CESC' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Community Action', 'Community action is when people work together to bring change.', '<p>Community action is when people work together to bring change.</p><h3>Key points</h3><ul><li>It can be through volunteering, advocacy, or organizing.</li><li>Participation should be voluntary and inclusive.</li><li>Small actions can lead to big changes.</li></ul><h3>Try this</h3><p>List three community action projects that students can join.</p>', 1 FROM courses c
WHERE c.course_code = 'HUMSS-CESC' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- GAS-HUMANITIES
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Introduction to the Humanities', 'The humanities study human culture, expression, and values.', '<p>The humanities study human culture, expression, and values.</p><h3>Key points</h3><ul><li>Disciplines include art, literature, music, philosophy, and history.</li><li>They help us understand what it means to be human.</li><li>They develop critical thinking and empathy.</li></ul><h3>Try this</h3><p>Choose an artwork and explain what it says about human experience.</p>', 1 FROM courses c
WHERE c.course_code = 'GAS-HUMANITIES' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Art Appreciation', 'Art appreciation is the ability to understand and value works of art.', '<p>Art appreciation is the ability to understand and value works of art.</p><h3>Key points</h3><ul><li>Elements of art include line, shape, color, texture, and space.</li><li>Principles include balance, contrast, and unity.</li><li>Context helps us understand an artwork&#x27;s meaning.</li></ul><h3>Try this</h3><p>Analyze a painting using the elements of art.</p>', 1 FROM courses c
WHERE c.course_code = 'GAS-HUMANITIES' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);

-- GAS-SOCIALSCI
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 1, 'Introduction to the Social Sciences', 'The social sciences study how people live and interact in society.', '<p>The social sciences study how people live and interact in society.</p><h3>Key points</h3><ul><li>They include sociology, anthropology, psychology, economics, and political science.</li><li>They use research methods to understand social behavior.</li><li>They help address social issues.</li></ul><h3>Try this</h3><p>List the social science disciplines and one question each would ask about poverty.</p>', 1 FROM courses c
WHERE c.course_code = 'GAS-SOCIALSCI' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 1);
INSERT INTO modules (course_id, module_number, title, description, content, published)
SELECT c.id, 2, 'Culture and Society', 'Culture is the shared way of life of a group of people.', '<p>Culture is the shared way of life of a group of people.</p><h3>Key points</h3><ul><li>Culture includes beliefs, values, norms, language, and material objects.</li><li>Ethnocentrism judges other cultures by one&#x27;s own standards.</li><li>Cultural relativism understands cultures on their own terms.</li></ul><h3>Try this</h3><p>Give an example of ethnocentrism and how to avoid it.</p>', 1 FROM courses c
WHERE c.course_code = 'GAS-SOCIALSCI' AND NOT EXISTS (SELECT 1 FROM modules x WHERE x.course_id = c.id AND x.module_number = 2);
