from google.analytics.data_v1beta import BetaAnalyticsDataClient
from google.analytics.data_v1beta.types import (
    DateRange,
    Dimension,
    Metric,
    RunReportRequest,
)


def sample_run_report(property_id):
    """Runs a report to get all-time page views for all URLs on a Google Analytics 4 property."""
    client = BetaAnalyticsDataClient()

    dimensions = [Dimension(name="pagePath")]
    metrics = [Metric(name="screenPageViews")]

    request = RunReportRequest(
        property=f"properties/{property_id}",
        dimensions=dimensions,
        metrics=metrics,
        date_ranges=[DateRange(start_date="2016-01-01", end_date="today")],  # '0001-01-01' as a proxy for all time
    )
    response = client.run_report(request)

    return response

import os
filedir = os.path.dirname(__file__)
cred_file = filedir + "/blog-view-counter-cred.json"
property_id_file = filedir + "/ga-property-id.txt"
db_file = filedir + "/../data.db"

if __name__ == "__main__":
    import sqlite3
    os.environ["GOOGLE_APPLICATION_CREDENTIALS"] = cred_file

    with open(property_id_file, "r") as f:
        property_id = f.read().strip()

    response = sample_run_report(property_id)

    # update the view count in the database
    conn = sqlite3.connect(db_file)
    for row in response.rows:
        url = row.dimension_values[0].value
        views = row.metric_values[0].value
        # if url matches the pattern: /post/<post_id>
        import re
        match = re.match(r"/post/([a-zA-Z0-9]+)", url)
        if match:
            post_id = match.group(1)
            # check if post_id exists in the database and update if it does
            cursor = conn.cursor()
            cursor.execute("SELECT views FROM posts WHERE post_id=?", (post_id,))
            result = cursor.fetchone()
            if result:
                cursor.execute("UPDATE posts SET views=? WHERE post_id=?", (views, post_id))
                conn.commit()
    conn.close()


