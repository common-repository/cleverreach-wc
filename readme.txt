=== Official CleverReach® Plugin for WooCommerce ===
Contributors: cleverreach43
Tags: newsletter, email, email marketing tool, newsletter marketing, software, marketing automation, integration, email automation, follow up newsletter
Requires at least: 4.7
Tested up to: 6.4.2
Requires PHP: 5.3
Stable tag: 3.4.1

Connect your WooCommerce store to our email software and say hello to successful and simple newsletter marketing – just like Spotify, Bugatti & DHL!

== Description ==
Spotify, Levi‘s, BMW and DHL create & send their newsletters with CleverReach®. Why? Because it’s simply clever. Do the same with your WooCommerce online shop! Easily and quickly design professional newsletters for your customers with the CleverReach® email marketing software – you don’t need any programming skills. Discover the extensive benefits of our integration solution for your WooCommerce online shop.
[youtube https://www.youtube.com/watch?v=AR-nGU6OjjM]

= Advantages of the CleverReach® newsletter plugin =
The plugin directly connects our CleverReach® Email Marketing Tool with your WooCommerce online shop and creates a recipient list with your newsletter recipients, buyers or other contacts - you can also select all of them.
All your important „WooCommerce“ data is instantly and continuously updated and added to the CleverReach® newsletter tool. The synchronization saves you valuable time and trouble. At the same time, you avoid errors that occur during a manual import or export.

The following data are continuously synchronized and updated:

* Newsletter subscriptions and unsubscribes
* First and last name, interests, gender, age, date of birth, address details…
* Purchased products, turnover, frequency of purchases, average shopping cart, order number, item number, product name, price, currency, amount…
* Details “Number of orders”, “Total expenses of all orders”, “Last order date“ are automatically saved in your recipient’s dataset and can be used for email marketing automation
* Product details of your WooCommerce products such as image, text, price, size, materials…

= MORE FEATURES FOR YOUR EMAIL MARKETING =

Additionally, you can:

* Using segments and tags: segments and tags are automatically created during the synchronization and attached to your recipients. Use e.g., tags to send your customers the latest products and offers via newsletter
* DOI signup forms: add and use a GDPR-compliant CleverReach Double-Opt-in signup forms in the checkout and registration area of your WooCommerce shop
* Abandoned cart emails: Remind customers and guest buyers of their filled shopping carts, if they didn’t complete the purchase process. In addition, you have an overview with the abandoned carts and can e.g. individually manage the emails.

Because it’s that simple

Make use of these valuable data to create personal offers your customers really want. Benefit from **more opens, more clicks** and **more conversions!** This is your key to more revenue: Your customers buy more stuff more frequently.

= THE KEY FEATURES OF CLEVERREACH =
* Simply create and send email newsletters in our multifunctional newsletter editors (e.g. newsletter or classic editor)
* Responsive, free templates for every industry and occasion
* Autoresponders, abandoned cart emails, follow-up emails and much more: easily create automated newsletter workflows with our automation tool THEA
* Analyze your success with our extensive reporting & tracking options and optimize your newsletters.
* Individually segment your recipients with tags and customer details
* Double-Opt-In signup and unsubscribe forms - GDPR compliant
* Highest security standards, GDPR-compliant, Email Marketing made in Germany
* Free Support
* Always the right price plan: customized, changeable at any time, no contract term - Scales for small and big contact lists: flex for occasional senders, essential for regular senders or enterprise for big players

Lots of additional features: Blacklist Check, A/B Split Tests, Design & Spam Tests, image processing RSS-Feeds, dynamic content, Import options, automatic bounce and unsubscribe management, personalized newsletter delivery, Social Media Integration, Reporting & Tracking (open, click and unsubscribe rates), Google Analytics Integration, Conversion Tracking, Lifecycle Email Marketing, Whitelisting, CSA - Certified Senders Alliance, Newsletter Client Testing, SPF, Senders Policy Framework, Email Authentication, SSL-encryption, surveys, CleverReach Plugin for WordPress (https://wordpress.org/plugins/cleverreach-wp/) and many more!

If you have any questions, contact our [support team](https://support.cleverreach.de/hc/en-us/requests/new) - they’ll be happy to assist you.

= Exclusively for WooCommerce customers =
Get a 10% discount off your CleverReach® rate for 12 months! Simply awesome: discount will automatically be provided when creating a new account.

= About CleverReach® =
CleverReach® was founded in 2007 and today is one of the internationally leading solution providers for email and newsletter marketing with more than 300,000 customers in 170 countries. A big plus of the German company based in Rastede, besides the user-friendly menu navigation of the software, is the competent customer service and the very fair price-performance ratio. CleverReach® complies with the highest privacy standards exceeding legal requirements.

== Installation ==
1. Upload the plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the `Plugins` menu in WordPress

== Screenshots ==
1. Automatically transfer your contacts to a recipient list
2. Get started after just a couple of seconds!
3. Get to know your customers better and send them newsletters based on their interests
4. Always be on the legally secure side with our GDPR-compliant signup forms
5. Insert your products in a free email template via drag & drop with just one click
6. Send out discount, transactional emails and more to your customers with email automation
7. All newsletter KPIs at a glance: track your success with email marketing key figures
8. Welcome to our Email Marketing Solution!

== Changelog ==

#### 3.4.1 - October 29, 2024

**Updates**

 - Database query optimization added

#### 3.4.0 - September 17, 2024

**Updates**

 - Add events buffer
 - Extend checkout using WooCommerce blocks
 - Migrate plugin configurations to separate table
 - Fix subscriber deactivation
 - Fix task cleanup task in archived table
 - Fix buyer not created when admin id logged in on checkout
 - Fix authorization issue

#### 3.3.4 - April 16, 2024

**Updates**

 - Implement group.deleted webhook handling

#### 3.3.2 - February 13, 2024

**Updates**

 - Fix push link in plugin dashboard page

#### 3.3.1 - January 17, 2024

**Updates**

 - Add compatibility with WooCommerce 8.3

#### 3.3.0 - December 20, 2023

**Updates**

 - Optimization: Avoidance of duplicate events during data exchange - Implementation of task-specific changes
 - Optimization: In certain constellations, the integration deleted segments in CR. This behavior has been fixed.
 - New blocklisting feature: Before importing, certain email addresses/domains can be excluded via a configuration window in the plugin. Example: Non-valid e-mail addresses generated at the POS (anonymous-customer-100000@example.com) - More information: https://support.cleverreach.de/hc/en-us/articles/14920866742418

#### 3.2.3 - December 05, 2023

**Updates**

 - Fix broken layout on checkout page

#### 3.2.2 - October 23, 2023

**Updates**

 - Bug fix when retrieving plugin log files
 - Fix send email settings overview on the Abandoned cart page

#### 3.2.1 - August 07, 2023

**Updates**

- Fix problem with tag service - Due to an earlier update regarding user roles, a fatal php error occurs in some constellations during the payment process
- Add compatibility with php >= 8.0 for initial sync

#### 3.2.0 - July 25, 2023

**Updates**

- Add compatibility with High Performance Order Storage

#### 3.1.13 - July 17, 2023

**Updates**

- Fix problem with updating role capabilities.
- Mark compatibility with WooCommerce 7.8.2.

#### 3.1.12 - March 20, 2023

**Updates**

- Fix enqueuing jquery on checkout.

#### 3.1.11 - January 11, 2023

**Updates**

- Fix alignment of text next to "Subscribe" button on checkout.

#### 3.1.10 - November 17, 2022

**Updates**

- Fix enqueue jquery.

#### 3.1.9 - September 27, 2022

**Updates**

- Changed product search image width size to width=600px.

#### 3.1.8 - August 15, 2022

**Updates**

- Add missing translations.

#### 3.1.7 - July 19, 2022

**Updates**

- Fixed the issue with the plugin database installation in SnapshotService.

#### 3.1.6 - July 18, 2022

**Rebranding**

- Updated the CleverReach icon and logo.

#### 3.1.5 - March 16, 2022

**Updates**

- Optimization: Update translation for initial synchronization status.

#### 3.1.4 - March 7, 2022

**Updates**

- Fix: Fixed dynamic content search.

#### 3.1.3 - February 9, 2022

**Updates**

- Optimization: Increased column data size for initial synchronization.

#### 3.1.2 - December 20, 2021

**Updates**

- Optimization: Increased product search image size in the new email editor.
- Optimization: Optimized database queries for fetching the data.
- Enhancement: Added async request option through support console.

#### 3.1.1 - November 16, 2021

**Updates**

- Updating ///PUSH blog links on plugin dashboard page.

#### 3.1.0 - October 25, 2021

**Updates**

- New feature: Send abandoned cart emails automatically with our Marketing Automation. The new plugin tab “Abandoned Cart” is available for you. You can create your own automation per store and increase your sales with this reminder email. You can find instructions on how to do this here: [https://support.cleverreach.de/hc/en-us/articles/4404430325266](https://support.cleverreach.de/hc/en-us/articles/4404430325266) - Note: The prerequisite for receiving abandoned cart emails is a double opt-in - This ensures complete DSGVO compliance.
- Additionally an overview of abandoned carts (If the abandoned cart feature is enabled in the CleverReach plugin). In the WooCommerce backend, the overview can be found via "Orders -> Abandoned Carts". Here you can view all open or completed shopping carts and the status of CleverReach "Abandoned cart emails" sent via our THEA Marketing Automation. The overview includes scheduled time, sender time, amount, email address, store, recovery status, and other features. In addition, the direct and immediate sending of an abandoned cart email can be started, or the sending can be canceled. A search & filter function can also be used to filter directly by email addresses or time periods.

#### 3.0.1 - July 27, 2021

**Optimization**

- Added more security validations.

#### 3.0.0 - June 23, 2021

**Updates**

- Complete UI redesign based on the WooCommerce CI.
- The user can decide for himself who will be actively transferred. Before the initial import of the data, the user can select whether only newsletter recipients, purchasing customers or other contacts should be transferred. Of course, everything can be selected. The data are created in CleverReach as active recipients in the "WooCommerce" group. Detailed instructions on the new procedure can be found at [https://support.cleverreach.de/hc/de/articles/360013571280](https://support.cleverreach.de/hc/de/articles/360013571280) - The initial synchronization can also be changed again later in the plugin via the plugin settings.
- Complete bidirectional synchronization (WooCommerce <--> CleverReach). The data is synchronized in both directions. If the address in the record at CleverReach changes, this is also transferred to WooCommerce. Unsubscribes in CleverReach system will also be transferred to WooCommerce, as usual, and set the customer in WooCommerce to "email status = inactive". Example: If the recipient added, updated, signed up for newsletter or unsubscribed in CleverReach, these changes will be passed to WooCommerce. The other way around from WooCommerce to CleverReach as well, of course.
- Tag based email creation process in the old CleverReach editor is available.
- New data fields: Number of orders & total spend across all orders. Example: Using the "Number of Orders" data field, I can filter by how many orders a KD has placed to specifically target KDs that have placed more than 50 orders. With the field "Total orders spent" I can, for example, write to all KDs who have spent more than 1000 euros in my store.
- For new customers, an email draft is automatically created to facilitate onboarding.
- Use of product categories, product tags, product attributes & product manufacturer as tags. Example: A customer has bought a black Adidas shoe from the category Shoes - This results in the following tags in CR for this recipient: WooCommerce-Category.Shoes // WooCommerce-Color.Black // WooCommerce-Manufacturer.Adidas - These tags can be used for a more targeted segmentation.
- Migration from v2 users to v3. Existing CleverReach users using the WooCommerce integration will be easily migrated to the new version without the need to re-register. Within the migration process, all WooCommerce customers who have activated the newsletter will be automatically transferred to CleverReach as active recipients. Other recipient groups (purchase customers or contacts) are neither synchronized nor deleted in CleverReach. The merchant can activate the synchronization of contacts or purchase customers under the "Sync Settings" tab at any time after the migration.

#### 1.4.8 - January 13, 2021

**Updates**

- New feature: Newsletter checkbox in checkout can be disabled.
- New feature: The text for the checkbox can be set by yourself via the new tab in the plugin “Settings.

#### 1.4.7 - August 12, 2020

**Updates**

- Optimization: Compatibility with WordPress 5.5 and WooCommerce 4.3.2.
- Optimization: Fix issue with session close.

#### 1.4.6 - July 9, 2020

**Updates**

- Optimization: Compatibility for WooCommerce 4.2.2.
- Optimization: Fix issue with session read and close.

#### 1.4.5 - Jun 2, 2020

**Updates**

- Optimization: Compatibility with WooCommerce 4.1.1 and WordPress 5.4.

#### 1.4.4 - May 20, 2020

**Updates**

- Optimization: Remove subscribe checkbox from checkout when user already subscribed to newsletter.
- Optimization: Added notification if connection with the CleverReach is lost.

#### 1.4.3 - April 06, 2020

**Updates**

- Optimization: The message “Account ID is null” was fixed. When the connection between WooCommerce and CleverReach is lost. The account ID is cached so that the login works correctly again.
- Optimization: Compatibility with WooCommerce 4.0.1

#### 1.4.2 - Feb 20, 2020

**Updates**

- Optimization: Compatibility for WooCommerce 3.9.2

#### 1.4.1 - Dec 11, 2019

**Updates**

- New feature: Products can be searched by ID in CleverReach emails.


#### 1.4.0 - Nov 14, 2019

**Updates**

- New feature: A new data field with the name "Last order date" is automatically created in CleverReach. The last order date of a customer is automatically stored in the data field if a new order has been made placed. For example, you can create a segment in CleverReach and trigger an automation chain for all those who have not bought anything for 90 days.
- New feature: If the import of customer data takes longer than 30 seconds, a message is displayed that the import will continue in the background. In the meantime, you can already create your mailing.
- Optimization: A verification was added to check whether the connection from WooCommerce to CleverReach still exists. As soon as the plugin is loaded, the plugin checks in the background whether the connection is still established. If the connection was lost, a message appears to reconnect to your CleverReach account.
- Optimization: When the connection with CleverReach is lost, all changes in the system will be tracked in the background. As soon as CleverReach user reconnect again, all changes will be synchronized to CleverReach.
- Optimization: Compatibility for Wordpress 5.3 and WooCommerce 3.8.0

#### 1.2.0 - Sep 16, 2019

**Updates**

- New feature: New CleverReach customers will get personalized dashboard in CleverReach. Example: "Hello John - Welcome to CleverReach".
- Optimization: CleverReach Log-in screen will be displayed as an iframe now.

#### 1.1.0 - May 27, 2019

**Updates**

- New function: Single-Sign-On (one-time login) has been added. After the data transfer you will be logged in directly to CleverReach and can create your e-mails
- Optimization: Faster transfer of data
- Optimization: Existing address data fields remain unchanged and are not overwritten with an empty data set
- Optimization: Import and use of order information within CleverReach improved

#### 1.0.1 - March 18, 2019

**Fixes**

- Fixed problem with getting WooCommerce plugin information.
