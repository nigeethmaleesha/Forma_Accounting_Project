<?php
declare(strict_types=1);

const BUSINESS_NAME = 'Trendy Tax Solutions';
const BUSINESS_URL = 'https://www.trendytaxsolutions.com.au';
const BUSINESS_EMAIL = 'info@trendytaxsolutions.com.au';
const BUSINESS_PHONE = '1300 968 554';
const BUSINESS_ADDRESS = '162 Macquarie Street, Hobart TAS 7000';

function service_data(): array {
    return [
      ['01','Tax & Compliance','Business Activity Statements, GST, Tax Returns And Clear Guidance On Staying Up To Date.','tax'],
      ['02','Business Advisory','Practical Advice On Your Business Structure, Growth Plans And Financial Decisions.','advisory'],
      ['03','Bookkeeping','Clean, Current Books That Give You The Confidence To Run Your Business.','books'],
      ['04','Payroll & Super','Payroll Support, Superannuation Administration And Single Touch Payroll Guidance.','payroll'],
      ['05','Business Plans','Thoughtful Planning For New Ventures, Established Businesses And What Comes Next.','plans'],
      ['06','SMSF Administration','Support With Financial Statements And Tax Returns For Self-Managed Super Funds.','smsf'],
      ['07','Virtual CFO','Strategic Financial Support, Insight And Planning Without A Full-Time CFO.','cfo'],
      ['08','Budgeting & Cash Flow','Understand Your Numbers, Prepare For Change And Plan With More Clarity.','budget'],
      ['09','Individual Tax Returns','Straightforward, Personal Tax Support For Individuals And Families.','individual']
    ];
}

function sample_posts(): array {
    // Editorial placeholders: deliberately not presented as current tax or legal advice.
    return [
      ['slug'=>'business-finances','tag'=>'Business Insights','date'=>'Editorial Preview','title'=>'Make More Room For What Matters In Business','excerpt'=>'A Practical Look At Turning Financial Information Into Better Daily Decisions.','theme'=>'lime'],
      ['slug'=>'bookkeeping-clarity','tag'=>'Bookkeeping','date'=>'Editorial Preview','title'=>'A Clearer Way To Stay On Top Of Your Books','excerpt'=>'The Simple Routines That Can Bring A Little More Clarity To Your Working Week.','theme'=>'peach'],
      ['slug'=>'forward-planning','tag'=>'Planning','date'=>'Editorial Preview','title'=>'Planning Ahead Starts With A Better Conversation','excerpt'=>'Why Thoughtful Questions Can Make Financial Planning Feel More Approachable.','theme'=>'lavender'],
    ];
}

function page_seo(string $page, ?string $slug = null): array {
    $defs = [
      'home'=>['Accounting, Tax & Business Advisory In Hobart | Trendy Tax Solutions','Discover Modern, Personable Accounting, Tax, Bookkeeping And Business Advisory Support In Hobart, Tasmania.'],
      'about'=>['About Our Accounting Firm | Trendy Tax Solutions Hobart','Get To Know Trendy Tax Solutions, Our Approach, Core Values And Commitment To Practical Accounting Advice.'],
      'services'=>['Tax, Bookkeeping & Business Advisory Services | Trendy Tax Solutions','Explore Tax And Compliance, Business Advisory, Bookkeeping, Payroll, SMSF, Virtual CFO And Individual Tax Services.'],
      'contact'=>['Contact Our Accountants In Hobart | Trendy Tax Solutions','Get In Touch With Trendy Tax Solutions In Hobart For Accounting, Business And Tax Enquiries.'],
      'journal'=>['Insights & Updates | Trendy Tax Solutions','Explore Accounting, Financial Planning, Bookkeeping And Business Insights From Trendy Tax Solutions.'],
      'portal'=>['Secure Client Portal | Trendy Tax Solutions','Access Your Client Account And Secure Document Portal.'],
      'privacy'=>['Privacy Information | Trendy Tax Solutions','Read Our Privacy Information And Learn About How To Contact Us About Your Personal Data.'],
      'article'=>['Insights | Trendy Tax Solutions','Read Practical Insights And Information From Trendy Tax Solutions.'],
      'admin'=>['Administration | Trendy Tax Solutions','Firm Administration And Content Management.'],
    ];
    return $defs[$page] ?? ['Page Not Found | Trendy Tax Solutions','Visit Trendy Tax Solutions For Accounting And Advisory Services.'];
}
