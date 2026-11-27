INSERT INTO marketplace_listings (
    seller_user_id,
    name,
    price,
    category,
    item_condition,
    description,
    image_path
)
SELECT
    demo_seller.user_id,
    sample.name,
    sample.price,
    sample.category,
    sample.item_condition,
    sample.description,
    sample.image_path
FROM users AS demo_seller
CROSS JOIN (
    SELECT
        'Data Structures in Java Book' AS name,
        550.00 AS price,
        'Books' AS category,
        'Good' AS item_condition,
        'Programming book with minimal highlights.' AS description,
        'images/datastructuresinjava.jpg' AS image_path
    UNION ALL SELECT
        'Modern Database Management Book',
        780.00,
        'Books',
        'Good',
        'Database management textbook useful for studying database concepts and systems.',
        'images/mdm.jpg'
    UNION ALL SELECT
        'OOP Reviewer',
        50.00,
        'Books',
        'Like New',
        'Printed reviewer for 1st semester midterm exam.',
        'images/oopreviewer.jpg'
    UNION ALL SELECT
        'PUPBC Uniform Set - Women',
        800.00,
        'Uniforms',
        'Like New',
        'Large Size. Uniform set with pants. Contact me for the other details.',
        'images/unifset.jpg'
    UNION ALL SELECT
        'PE Uniform Top only',
        320.00,
        'Uniforms',
        'Good',
        'Size medium. All goods pa.',
        'images/peunif.jpg'
    UNION ALL SELECT
        'CASIO Scientific Calculator',
        700.00,
        'Electronics',
        'Good',
        'Scientific calculator in good working condition, suitable for school and mathematical use.',
        'images/calc.jpg'
    UNION ALL SELECT
        'Logitech Wireless Mouse',
        680.00,
        'Electronics',
        'Good',
        'Wireless mouse in good working condition. Suitable for laptops, computers, and everyday school use.',
        'images/mouse.jpg'
    UNION ALL SELECT
        '1 Ream A4 Hard Copy Bond paper',
        280.00,
        'School Supplies',
        'New',
        '1 ream of A4 bond paper containing 500 sheets. Suitable for printing school requirements and documents.',
        'images/bndpaper.jpg'
    UNION ALL SELECT
        'Mica Paper Set',
        280.00,
        'School Supplies',
        'New',
        'Brand new mica paper set suitable for school projects, covers, and other printing needs.',
        'images/mica.jpg'
    UNION ALL SELECT
        'Homemade Cookies',
        10.00,
        'Others',
        'New',
        'Freshly made homemade cookies available in Matcha, Choco, and Red Velvet flavors. PHP 10 per piece.',
        'images/cookie.jpg'
) AS sample
WHERE demo_seller.username = 'pupmarket_test_seller'
  AND NOT EXISTS (
      SELECT 1
      FROM marketplace_listings AS existing
      WHERE existing.seller_user_id = demo_seller.user_id
        AND existing.name = sample.name
        AND existing.image_path = sample.image_path
  );
