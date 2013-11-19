Given /^I have PNP data for "(.+)"/ do |object|
	if object =~ /;/ then
		objs = object.split(";")
		host = objs[0]
		service = objs[1]
	else
		service = "_HOST_"
		host = object
	end
	host.gsub!(/[ :\/\\]/, '_')
	service.gsub!(/[ :\/\\]/, '_')

	FileUtils.mkdir_p("/opt/monitor/op5/pnp/perfdata/" + host)
	FileUtils.touch("/opt/monitor/op5/pnp/perfdata/" + host + "/" + service + ".xml")
end

When /^I enter the time in (\d) minutes into "(.+)"$/ do |minutes, selector|
	require('date')
	fill_in(selector, :with => (Time.now + minutes.to_i * 60).strftime('%F %T'))
end

# Because all our projects have their own helptext implementation...
Then /^all helptexts should be defined$/ do
  all(:css, '.helptext_target', :visible => true).each { | elem |
    elem.click
    page.should have_css(".qtip-content", :visible => true)
    # "This helptext (%s) is not translated yet" is only printed by convention, but it appears we follow it
    page.should have_no_content "This helptext"
    # Hide helptext - only doable by clicking elsewhere
    page.find(".logo").click
    page.should have_no_css(".qtip-content", :visible => true)
  }
end
